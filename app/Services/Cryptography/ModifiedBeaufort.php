<?php

namespace App\Services\Cryptography;

/**
 * ModifiedBeaufort
 * ----------------
 * Varian Beaufort yang dimodifikasi:
 *
 *   1. Kunci tidak berulang, melainkan keystream dari Blum-Blum-Shub
 *      (satu byte keystream untuk setiap byte data).
 *   2. Ditambahkan umpan balik ciphertext sebelumnya (chaining) sehingga
 *      satu byte ciphertext ikut memengaruhi byte berikutnya.
 *
 *   Enkripsi : C[i] = (K[i] - P[i] - C[i-1]) mod 256
 *   Dekripsi : P[i] = (K[i] - C[i] - C[i-1]) mod 256      dengan C[-1] = 0
 *
 * Objek ini menyimpan state (posisi BBS dan C[i-1]) sehingga data besar dapat
 * diproses potong demi potong dengan memanggil encrypt()/decrypt() berulang.
 * Satu objek hanya boleh dipakai untuk satu arah (enkripsi ATAU dekripsi).
 */
class ModifiedBeaufort
{
    private int $previous = 0;

    public function __construct(private BlumBlumShub $keystream)
    {
    }

    public function encrypt(string $plaintext): string
    {
        $length = strlen($plaintext);
        $key = $this->keystream->generate($length);
        $output = '';

        for ($i = 0; $i < $length; $i++) {
            $c = (ord($key[$i]) - ord($plaintext[$i]) - $this->previous) & 0xFF;

            $this->previous = $c;
            $output .= chr($c);
        }

        return $output;
    }

    public function decrypt(string $ciphertext): string
    {
        $length = strlen($ciphertext);
        $key = $this->keystream->generate($length);
        $output = '';

        for ($i = 0; $i < $length; $i++) {
            $c = ord($ciphertext[$i]);

            $output .= chr((ord($key[$i]) - $c - $this->previous) & 0xFF);
            $this->previous = $c;
        }

        return $output;
    }
}
