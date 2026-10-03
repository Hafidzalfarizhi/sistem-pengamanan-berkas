<?php

namespace App\Services\Cryptography;

use App\Services\Cryptography\Exceptions\CorruptedDataException;
use InvalidArgumentException;

/**
 * RedundancyScheme
 * ----------------
 * Dekripsi Rabin menghasilkan EMPAT akar kuadrat modular, hanya satu yang
 * merupakan plaintext asli. Skema redundansi memberi "tanda pengenal" pada
 * plaintext sebelum dienkripsi agar akar yang benar dapat dikenali.
 *
 * Susunan blok (modulus n sepanjang L byte):
 *
 *   m' = DATA || SALINAN 8 BYTE TERAKHIR DATA          (L - 1 byte)
 *
 * sehingga m' < 2^(8(L-1)) < n. Setelah dekripsi, akar diubah menjadi L byte
 * (dipadatkan nol di kiri) dan dianggap benar bila:
 *
 *   a. byte pertama = 0x00               (peluang acak 2^-8)
 *   b. 8 byte terakhir = 8 byte sebelumnya (peluang acak 2^-64)
 *
 * Peluang akar salah lolos kedua uji ~ 2^-72.
 */
class RedundancyScheme
{
    public const REDUNDANCY_BYTES = 8;

    /** Banyak byte data yang dapat dimuat satu blok untuk modulus $modulusBytes byte. */
    public function dataCapacity(int $modulusBytes): int
    {
        return $modulusBytes - 1 - self::REDUNDANCY_BYTES;
    }

    /** Menambahkan redundansi. $data harus tepat sepanjang dataCapacity(). */
    public function embed(string $data, int $modulusBytes): string
    {
        if (strlen($data) !== $this->dataCapacity($modulusBytes)) {
            throw new InvalidArgumentException('Panjang data blok tidak sesuai kapasitas.');
        }

        return $data . substr($data, -self::REDUNDANCY_BYTES);
    }

    /** Apakah kandidat (tepat L byte) memenuhi pola redundansi? */
    public function isValid(string $candidate): bool
    {
        $length = strlen($candidate);
        if ($length < self::REDUNDANCY_BYTES * 2 + 1) {
            return false;
        }

        if ($candidate[0] !== "\0") {
            return false;
        }

        $tail = substr($candidate, -self::REDUNDANCY_BYTES);
        $beforeTail = substr($candidate, -2 * self::REDUNDANCY_BYTES, self::REDUNDANCY_BYTES);

        return $tail === $beforeTail;
    }

    /** Mengambil bagian data dari kandidat yang sudah valid. */
    public function extract(string $candidate): string
    {
        return substr($candidate, 1, -self::REDUNDANCY_BYTES);
    }

    /**
     * Memilih akar yang benar dari empat kandidat.
     *
     * @param  string[]  $candidates  empat akar dalam bentuk byte (panjang L)
     * @throws CorruptedDataException bila tidak ada akar yang lolos
     */
    public function selectRoot(array $candidates): string
    {
        foreach ($candidates as $candidate) {
            if ($this->isValid($candidate)) {
                return $this->extract($candidate);
            }
        }

        throw new CorruptedDataException('Tidak ada akar Rabin yang memenuhi skema redundansi.');
    }
}
