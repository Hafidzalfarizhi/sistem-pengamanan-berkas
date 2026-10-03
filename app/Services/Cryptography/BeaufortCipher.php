<?php

namespace App\Services\Cryptography;

use InvalidArgumentException;

/**
 * BeaufortCipher
 * --------------
 * Beaufort Cipher standar yang diperluas ke alfabet 256 simbol (satu byte),
 * sehingga dapat memproses data biner apa pun, bukan hanya huruf A-Z.
 *
 *   Enkripsi : C[i] = (K[i mod m] - P[i]) mod 256
 *   Dekripsi : P[i] = (K[i mod m] - C[i]) mod 256
 *
 * Beaufort bersifat resiprokal: rumus enkripsi dan dekripsi sama.
 * Parameter $keyOffset memungkinkan pemrosesan bertahap (per potongan data)
 * tanpa memutus urutan pengulangan kunci.
 */
class BeaufortCipher
{
    public function encrypt(string $plaintext, string $key, int $keyOffset = 0): string
    {
        return $this->transform($plaintext, $key, $keyOffset);
    }

    public function decrypt(string $ciphertext, string $key, int $keyOffset = 0): string
    {
        return $this->transform($ciphertext, $key, $keyOffset);
    }

    private function transform(string $data, string $key, int $keyOffset): string
    {
        $keyLength = strlen($key);
        if ($keyLength === 0) {
            throw new InvalidArgumentException('Kunci tidak boleh kosong.');
        }

        $length = strlen($data);
        $output = '';

        for ($i = 0; $i < $length; $i++) {
            $k = ord($key[($keyOffset + $i) % $keyLength]);
            $p = ord($data[$i]);

            $output .= chr(($k - $p) & 0xFF); // (K - P) mod 256
        }

        return $output;
    }
}
