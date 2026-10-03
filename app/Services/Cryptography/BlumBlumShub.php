<?php

namespace App\Services\Cryptography;

use InvalidArgumentException;

/**
 * BlumBlumShub
 * ------------
 * Pembangkit bilangan pseudorandom Blum-Blum-Shub (BBS):
 *
 *   N      = P * Q          (P, Q prima, P = Q = 3 mod 4)
 *   x[0]   = seed^2 mod N   (seed koprima dengan N)
 *   x[i+1] = x[i]^2 mod N
 *
 * Pada setiap iterasi diambil BYTES_PER_ITERATION byte paling rendah
 * (x mod 2^32 = 4 byte) sebagai keystream.
 *
 * CATATAN PARAMETER: bukti keamanan BBS mengizinkan log2(log2 N) bit per
 * iterasi (9 bit untuk N 512 bit). Mengambil 32 bit per iterasi adalah
 * kompromi performa karena PHP + bcmath lambat; nilai ini dapat diubah
 * pada konstanta di bawah (1 = paling konservatif) bila diperlukan untuk
 * keperluan penelitian.
 *
 * x mod 2^32 dihitung dari 32 digit desimal terakhir karena 10^32 habis
 * dibagi 2^32.
 */
class BlumBlumShub
{
    public const BYTES_PER_ITERATION = 4;

    private string $state;
    private string $buffer = '';
    private array $ctx;

    public function __construct(private string $modulus, string $x0)
    {
        if (bccomp($modulus, '3', 0) < 0 || bccomp($x0, '1', 0) <= 0 || bccomp($x0, $modulus, 0) >= 0) {
            throw new InvalidArgumentException('Parameter BBS tidak valid.');
        }

        $this->state = $x0;
        $this->ctx = BigMath::barrettContext($modulus);
    }

    /**
     * Membangkitkan parameter BBS baru.
     *
     * @return array{modulus: string, seed: string} bilangan desimal
     */
    public static function generateParameters(int $primeBits = 256): array
    {
        do {
            $p = BigMath::randomPrime($primeBits);
            $q = BigMath::randomPrime($primeBits);
        } while ($p === $q);

        $modulus = bcmul($p, $q, 0);

        // x0 = r^2 mod N dengan gcd(r, N) = 1
        do {
            $r = bcmod(BigMath::fromBytes(random_bytes(strlen(BigMath::toBytes($modulus)))), $modulus, 0);
            $x0 = bcmod(bcmul($r, $r, 0), $modulus, 0);
        } while (bccomp($r, '1', 0) <= 0
            || BigMath::gcd($r, $modulus) !== '1'
            || bccomp($x0, '1', 0) <= 0);

        return ['modulus' => $modulus, 'seed' => $x0];
    }

    /** Keystream sepanjang $length byte; urutan sama berapa pun ukuran potongannya. */
    public function generate(int $length): string
    {
        while (strlen($this->buffer) < $length) {
            $this->state = BigMath::barrettReduce(bcmul($this->state, $this->state, 0), $this->ctx);

            $low = (int) bcmod(substr($this->state, -32), '4294967296', 0);
            $this->buffer .= pack('N', $low);
        }

        $stream = substr($this->buffer, 0, $length);
        $this->buffer = substr($this->buffer, $length);

        return $stream;
    }

    /** Satu byte keystream berikutnya (0..255). */
    public function nextByte(): int
    {
        return ord($this->generate(1));
    }
}
