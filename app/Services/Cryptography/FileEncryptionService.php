<?php

namespace App\Services\Cryptography;

use App\Services\Cryptography\Exceptions\CorruptedDataException;
use App\Services\Cryptography\Exceptions\InvalidKeyException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * FileEncryptionService
 * ---------------------
 * Menghubungkan seluruh komponen kriptografi untuk mengenc/dekripsi file biner.
 *
 * ALUR ENKRIPSI
 *   1. Bangkitkan pasangan kunci Rabin (p, q, n) dan parameter BBS (N, x0).
 *   2. Isi file dienkripsi dengan Modified Beaufort memakai keystream BBS(N, x0).
 *   3. Parameter BBS (N, x0) + checksum dibungkus dengan Rabin (kunci publik n).
 *   4. Kunci privat Rabin (p, q) dilindungi dengan Beaufort standar memakai
 *      password + salt sebagai kunci.
 *
 * ALUR DEKRIPSI
 *   1. Beaufort membuka (p, q) dengan password + salt. Jika p*q != n,
 *      password salah -> InvalidKeyException.
 *   2. Rabin + RedundancyScheme membuka (N, x0, checksum).
 *   3. Modified Beaufort + BBS mengembalikan isi file; checksum diverifikasi.
 *
 * FORMAT FILE (.sfl)
 *   "SFL1"                      4  byte  penanda
 *   salt                       16  byte
 *   panjang n (uint16)          2  byte
 *   n                         128  byte  modulus Rabin (publik)
 *   (p||q) terlindungi        128  byte  hasil Beaufort
 *   jumlah blok Rabin (uint8)   1  byte
 *   blok Rabin            k x 128  byte  membungkus N || x0 || checksum
 *   panjang isi (uint64)        8  byte
 *   isi terenkripsi             ...      Modified Beaufort
 *
 * Isi (sebelum dienkripsi) = uint16 panjang nama | nama file asli | data file.
 * Nama file asli ikut terenkripsi sehingga tidak bocor pada file .sfl.
 *
 * File dibaca/ditulis per potongan sehingga tidak dimuat utuh ke memori.
 */
class FileEncryptionService
{
    public const MAGIC = 'SFL1';
    public const SALT_LENGTH = 16;
    public const RABIN_PRIME_BITS = 512;
    public const BBS_PRIME_BITS = 256;
    public const PRIME_BYTES = 64;      // 512 bit
    public const BBS_FIELD_BYTES = 64;  // N dan x0 masing-masing 512 bit
    public const MODULUS_BYTES = 128;   // n Rabin 1024 bit
    public const MAX_NAME_BYTES = 255;

    public function __construct(
        private BeaufortCipher $beaufort = new BeaufortCipher(),
        private RabinCryptosystem $rabin = new RabinCryptosystem(),
        private int $chunkSize = 8192,
    ) {
    }

    /**
     * @return array{original_size: int, processed_size: int}
     */
    public function encryptFile(string $inputPath, string $outputPath, string $originalName, string $password): array
    {
        if ($password === '') {
            throw new InvalidArgumentException('Password/kunci wajib diisi.');
        }
        if (! is_file($inputPath) || ! is_readable($inputPath)) {
            throw new RuntimeException('File sumber tidak dapat dibaca.');
        }

        $originalSize = (int) filesize($inputPath);
        $name = mb_strcut($originalName, 0, self::MAX_NAME_BYTES, 'UTF-8');
        $meta = pack('n', strlen($name)) . $name;
        $plainLength = strlen($meta) + $originalSize;

        $input = fopen($inputPath, 'rb');
        $output = fopen($outputPath, 'wb');

        try {
            if ($input === false || $output === false) {
                throw new RuntimeException('Gagal membuka file.');
            }

            // Lintasan 1: checksum isi (nama + data)
            [$a, $b] = [1, 0];
            $this->adlerUpdate($meta, $a, $b);
            while (! feof($input)) {
                $chunk = fread($input, $this->chunkSize);
                if ($chunk === false) {
                    throw new RuntimeException('Gagal membaca file.');
                }
                $this->adlerUpdate($chunk, $a, $b);
            }
            $checksum = (($b << 16) | $a) & 0xFFFFFFFF;
            rewind($input);

            // Parameter kriptografi
            $rabinKey = $this->rabin->generateKeyPair(self::RABIN_PRIME_BITS);
            $bbs = BlumBlumShub::generateParameters(self::BBS_PRIME_BITS);

            // Rabin membungkus parameter BBS
            $payload = BigMath::toBytes($bbs['modulus'], self::BBS_FIELD_BYTES)
                . BigMath::toBytes($bbs['seed'], self::BBS_FIELD_BYTES)
                . pack('N', $checksum);
            $rabinCipher = $this->rabin->encrypt($payload, $rabinKey['n']);

            // Beaufort melindungi kunci privat Rabin
            $salt = random_bytes(self::SALT_LENGTH);
            $privatePart = BigMath::toBytes($rabinKey['p'], self::PRIME_BYTES)
                . BigMath::toBytes($rabinKey['q'], self::PRIME_BYTES);
            $protectedKey = $this->beaufort->encrypt($privatePart, $password . $salt);

            $header = self::MAGIC
                . $salt
                . pack('n', self::MODULUS_BYTES)
                . BigMath::toBytes($rabinKey['n'], self::MODULUS_BYTES)
                . $protectedKey
                . pack('C', intdiv(strlen($rabinCipher), self::MODULUS_BYTES))
                . $rabinCipher
                . pack('J', $plainLength);

            $this->writeAll($output, $header);

            // Lintasan 2: enkripsi isi dengan Modified Beaufort + BBS
            $cipher = new ModifiedBeaufort(new BlumBlumShub($bbs['modulus'], $bbs['seed']));
            $this->writeAll($output, $cipher->encrypt($meta));

            while (! feof($input)) {
                $chunk = fread($input, $this->chunkSize);
                if ($chunk === false) {
                    throw new RuntimeException('Gagal membaca file.');
                }
                if ($chunk !== '') {
                    $this->writeAll($output, $cipher->encrypt($chunk));
                }
            }

            fclose($input);
            fclose($output);
        } catch (Throwable $e) {
            is_resource($input) && fclose($input);
            is_resource($output) && fclose($output);
            is_file($outputPath) && @unlink($outputPath);

            throw $e;
        }

        clearstatcache(true, $outputPath);

        return [
            'original_size' => $originalSize,
            'processed_size' => (int) filesize($outputPath),
        ];
    }

    /**
     * @return array{original_name: string, size: int}
     * @throws InvalidKeyException    password salah
     * @throws CorruptedDataException file rusak / bukan file .sfl
     */
    public function decryptFile(string $inputPath, string $outputPath, string $password): array
    {
        if (! is_file($inputPath) || ! is_readable($inputPath)) {
            throw new RuntimeException('File sumber tidak dapat dibaca.');
        }

        $fileSize = (int) filesize($inputPath);
        $input = fopen($inputPath, 'rb');
        $output = null;

        try {
            if ($input === false) {
                throw new RuntimeException('Gagal membuka file.');
            }

            // --- Baca header ---
            if ($this->readExact($input, 4) !== self::MAGIC) {
                throw new CorruptedDataException('Penanda file tidak dikenali.');
            }

            $salt = $this->readExact($input, self::SALT_LENGTH);
            $modulusLength = unpack('n', $this->readExact($input, 2))[1];
            if ($modulusLength !== self::MODULUS_BYTES) {
                throw new CorruptedDataException('Panjang modulus tidak valid.');
            }

            $nBytes = $this->readExact($input, self::MODULUS_BYTES);
            $protectedKey = $this->readExact($input, self::PRIME_BYTES * 2);
            $blockCount = unpack('C', $this->readExact($input, 1))[1];
            if ($blockCount < 1 || $blockCount > 8) {
                throw new CorruptedDataException('Jumlah blok tidak valid.');
            }

            $rabinCipher = $this->readExact($input, $blockCount * self::MODULUS_BYTES);
            $bodyLength = unpack('J', $this->readExact($input, 8))[1];
            $headerLength = (int) ftell($input);

            if ($bodyLength < 2 || $headerLength + $bodyLength !== $fileSize) {
                throw new CorruptedDataException('Ukuran file tidak sesuai.');
            }

            // --- Buka kunci privat Rabin dengan Beaufort; verifikasi password ---
            $n = BigMath::fromBytes($nBytes);
            $privatePart = $this->beaufort->decrypt($protectedKey, $password . $salt);
            $p = BigMath::fromBytes(substr($privatePart, 0, self::PRIME_BYTES));
            $q = BigMath::fromBytes(substr($privatePart, self::PRIME_BYTES));

            if (bccomp($p, '1', 0) <= 0 || bccomp($q, '1', 0) <= 0 || bcmul($p, $q, 0) !== $n) {
                throw new InvalidKeyException('Password atau kunci tidak valid.');
            }

            // --- Rabin + redundansi membuka parameter BBS ---
            $payload = substr($this->rabin->decrypt($rabinCipher, $p, $q), 0, self::BBS_FIELD_BYTES * 2 + 4);
            $modulus = BigMath::fromBytes(substr($payload, 0, self::BBS_FIELD_BYTES));
            $seed = BigMath::fromBytes(substr($payload, self::BBS_FIELD_BYTES, self::BBS_FIELD_BYTES));
            $expectedChecksum = unpack('N', substr($payload, self::BBS_FIELD_BYTES * 2, 4))[1];

            try {
                $keystream = new BlumBlumShub($modulus, $seed);
            } catch (InvalidArgumentException) {
                throw new CorruptedDataException('Parameter BBS tidak valid.');
            }

            // --- Dekripsi isi ---
            $cipher = new ModifiedBeaufort($keystream);
            $output = fopen($outputPath, 'wb');
            if ($output === false) {
                throw new RuntimeException('Gagal membuat file hasil.');
            }

            $remaining = $bodyLength;
            $pending = '';
            $name = null;
            $written = 0;
            [$a, $b] = [1, 0];

            while ($remaining > 0) {
                $chunk = $this->readExact($input, min($this->chunkSize, $remaining));
                $remaining -= strlen($chunk);

                $plain = $cipher->decrypt($chunk);
                $this->adlerUpdate($plain, $a, $b);

                if ($name === null) {
                    $pending .= $plain;
                    if (strlen($pending) < 2) {
                        continue;
                    }

                    $nameLength = unpack('n', substr($pending, 0, 2))[1];
                    if ($nameLength > self::MAX_NAME_BYTES) {
                        throw new CorruptedDataException('Nama file tidak valid.');
                    }
                    if (strlen($pending) < 2 + $nameLength) {
                        continue;
                    }

                    $name = substr($pending, 2, $nameLength);
                    $plain = substr($pending, 2 + $nameLength);
                    $pending = '';
                }

                if ($plain !== '') {
                    $this->writeAll($output, $plain);
                    $written += strlen($plain);
                }
            }

            if ($name === null) {
                throw new CorruptedDataException('Isi file tidak lengkap.');
            }

            $checksum = (($b << 16) | $a) & 0xFFFFFFFF;
            if ($checksum !== $expectedChecksum) {
                throw new CorruptedDataException('Checksum isi tidak cocok.');
            }

            fclose($input);
            fclose($output);
        } catch (Throwable $e) {
            is_resource($input) && fclose($input);
            is_resource($output) && fclose($output);
            is_file($outputPath) && @unlink($outputPath);

            throw $e;
        }

        return ['original_name' => $name, 'size' => $written];
    }

    /** Adler-32 (ditulis sendiri) untuk memeriksa keutuhan isi file. */
    private function adlerUpdate(string $data, int &$a, int &$b): void
    {
        if ($data === '') {
            return;
        }

        foreach (unpack('C*', $data) as $byte) {
            $a = ($a + $byte) % 65521;
            $b = ($b + $a) % 65521;
        }
    }

    /** @param resource $handle */
    private function readExact($handle, int $length): string
    {
        $data = '';
        while (strlen($data) < $length) {
            $part = fread($handle, $length - strlen($data));
            if ($part === false || $part === '') {
                throw new CorruptedDataException('File berakhir lebih awal dari yang seharusnya.');
            }
            $data .= $part;
        }

        return $data;
    }

     /** @param resource $handle */
    private function writeAll($handle, string $data): void
    {
        $length = strlen($data);
        $written = 0;

        while ($written < $length) {
            $result = fwrite($handle, substr($data, $written));
            if ($result === false || $result === 0) {
                throw new RuntimeException('Gagal menulis file.');
            }
            $written += $result;
        }
    }
}
