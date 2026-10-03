<?php

namespace App\Services\Cryptography;

use App\Services\Cryptography\Exceptions\CorruptedDataException;
use InvalidArgumentException;

/**
 * RabinCryptosystem
 * -----------------
 * Kunci publik  : n = p * q
 * Kunci privat  : p, q (prima, p = q = 3 mod 4)
 *
 * Enkripsi : c = m^2 mod n
 * Dekripsi : hitung akar kuadrat c modulo p dan modulo q, gabungkan dengan
 *            Chinese Remainder Theorem -> empat akar. Akar yang benar dipilih
 *            oleh RedundancyScheme.
 *
 * Karena p = 3 (mod 4), akar kuadrat modulo p adalah c^((p+1)/4) mod p.
 *
 * Data yang lebih panjang dari satu blok dipecah menjadi blok-blok sebesar
 * RedundancyScheme::dataCapacity(); setiap blok dienkripsi terpisah.
 */
class RabinCryptosystem
{
    public function __construct(private RedundancyScheme $redundancy = new RedundancyScheme())
    {
    }

    /**
     * Membangkitkan pasangan kunci.
     *
     * @return array{p: string, q: string, n: string} bilangan desimal
     */
    public function generateKeyPair(int $primeBits = 512): array
    {
        do {
            $p = BigMath::randomPrime($primeBits);
            $q = BigMath::randomPrime($primeBits);
        } while ($p === $q);

        return ['p' => $p, 'q' => $q, 'n' => bcmul($p, $q, 0)];
    }

    /** Panjang modulus dalam byte. */
    public function modulusBytes(string $n): int
    {
        return strlen(BigMath::toBytes($n));
    }

    /**
     * Enkripsi data sembarang panjang. Blok terakhir dipadatkan byte nol;
     * pemanggil wajib menyimpan panjang data asli sendiri.
     *
     * @return string gabungan ciphertext, tiap blok L byte
     */
    public function encrypt(string $data, string $n): string
    {
        $modulusBytes = $this->modulusBytes($n);
        $capacity = $this->redundancy->dataCapacity($modulusBytes);

        if ($capacity <= 0) {
            throw new InvalidArgumentException('Modulus Rabin terlalu kecil.');
        }

        $cipher = '';
        $length = strlen($data);

        for ($offset = 0; $offset < $length; $offset += $capacity) {
            $block = str_pad(substr($data, $offset, $capacity), $capacity, "\0");
            $cipher .= $this->encryptBlock($block, $n, $modulusBytes);
        }

        return $cipher;
    }

    /**
     * Dekripsi gabungan blok ciphertext. Hasil sudah dipadatkan byte nol pada
     * blok terakhir (sesuai encrypt()).
     *
     * @throws CorruptedDataException
     */
    public function decrypt(string $cipher, string $p, string $q): string
    {
        $n = bcmul($p, $q, 0);
        $modulusBytes = $this->modulusBytes($n);

        if ($cipher === '' || strlen($cipher) % $modulusBytes !== 0) {
            throw new CorruptedDataException('Panjang ciphertext Rabin tidak valid.');
        }

        $data = '';
        foreach (str_split($cipher, $modulusBytes) as $block) {
            $data .= $this->decryptBlock($block, $p, $q, $modulusBytes);
        }

        return $data;
    }

    /** c = m'^2 mod n untuk satu blok; m' = data + redundansi. */
    public function encryptBlock(string $block, string $n, ?int $modulusBytes = null): string
    {
        $modulusBytes ??= $this->modulusBytes($n);

        $message = $this->redundancy->embed($block, $modulusBytes);
        $m = BigMath::fromBytes($message);
        $c = bcmod(bcmul($m, $m, 0), $n, 0);

        return BigMath::toBytes($c, $modulusBytes);
    }

    /**
     * Dekripsi satu blok: cari empat akar lalu pilih dengan skema redundansi.
     *
     * @throws CorruptedDataException
     */
    public function decryptBlock(string $cipherBlock, string $p, string $q, ?int $modulusBytes = null): string
    {
        $n = bcmul($p, $q, 0);
        $modulusBytes ??= $this->modulusBytes($n);

        $c = BigMath::fromBytes($cipherBlock);
        if (bccomp($c, $n, 0) >= 0) {
            throw new CorruptedDataException('Blok ciphertext Rabin tidak valid.');
        }

        $candidates = [];
        foreach ($this->computeRoots($c, $p, $q) as $root) {
            $candidates[] = BigMath::toBytes($root, $modulusBytes);
        }

        return $this->redundancy->selectRoot($candidates);
    }

    /**
     * Empat akar kuadrat c modulo n = p*q (Chinese Remainder Theorem).
     *
     * @return string[] empat bilangan desimal
     */
    public function computeRoots(string $c, string $p, string $q): array
    {
        $n = bcmul($p, $q, 0);

        // Akar modulo p dan q (p, q = 3 mod 4)
        $mp = BigMath::powMod($c, bcdiv(bcadd($p, '1', 0), '4', 0), $p);
        $mq = BigMath::powMod($c, bcdiv(bcadd($q, '1', 0), '4', 0), $q);

        // Koefisien CRT: yp * p + yq * q = 1
        $yp = BigMath::modInverse($p, $q);
        $yq = BigMath::modInverse($q, $p);

        if ($yp === null || $yq === null) {
            throw new CorruptedDataException('Kunci Rabin tidak valid.');
        }

        $termP = bcmul(bcmul($yp, $p, 0), $mq, 0); // = mq (mod q), 0 (mod p)
        $termQ = bcmul(bcmul($yq, $q, 0), $mp, 0); // = mp (mod p), 0 (mod q)

        $r1 = bcmod(bcadd($termP, $termQ, 0), $n, 0);
        $r3 = bcmod(bcsub($termP, $termQ, 0), $n, 0);
        if (bccomp($r3, '0', 0) < 0) {
            $r3 = bcadd($r3, $n, 0);
        }

        return [
            $r1,
            bcmod(bcsub($n, $r1, 0), $n, 0),
            $r3,
            bcmod(bcsub($n, $r3, 0), $n, 0),
        ];
    }
}
