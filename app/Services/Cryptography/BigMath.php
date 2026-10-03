<?php

namespace App\Services\Cryptography;

use InvalidArgumentException;

/**
 * BigMath
 * -------
 * Aritmetika bilangan bulat besar yang dibutuhkan oleh BBS dan Rabin.
 *
 * Integer native PHP hanya 64-bit, sedangkan modulus Rabin/BBS harus ratusan
 * bit. Kelas ini memakai ekstensi bcmath HANYA sebagai operasi dasar
 * (bcadd, bcsub, bcmul, bcdiv, bcmod, bccomp). Seluruh algoritma di atasnya
 * ditulis sendiri di sini:
 *
 *   - konversi byte <-> bilangan bulat
 *   - perpangkatan modular (square-and-multiply)
 *   - FPB / GCD (algoritma Euclid)
 *   - invers modular (Extended Euclid)
 *   - uji keprimaan Miller-Rabin
 *   - pembangkitan bilangan prima acak
 *
 * Semua bilangan direpresentasikan sebagai string desimal non-negatif.
 * bcpowmod() dan bcsqrt() TIDAK dipakai.
 */
class BigMath
{
    /** @var int[]|null */
    private static ?array $smallPrimes = null;

    /** Byte string (big-endian) -> bilangan desimal. */
    public static function fromBytes(string $bytes): string
    {
        $result = '0';
        $length = strlen($bytes);

        for ($i = 0; $i < $length; $i++) {
            $result = bcadd(bcmul($result, '256', 0), (string) ord($bytes[$i]), 0);
        }

        return $result;
    }

    /** Bilangan desimal -> byte string (big-endian), opsional dipadatkan ke $length byte. */
    public static function toBytes(string $number, int $length = 0): string
    {
        $bytes = '';

        while (bccomp($number, '0', 0) > 0) {
            $bytes = chr((int) bcmod($number, '256', 0)) . $bytes;
            $number = bcdiv($number, '256', 0);
        }

        if ($length > 0) {
            if (strlen($bytes) > $length) {
                throw new InvalidArgumentException('Bilangan melebihi panjang byte yang diminta.');
            }
            $bytes = str_pad($bytes, $length, "\0", STR_PAD_LEFT);
        }

        return $bytes === '' && $length === 0 ? "\0" : $bytes;
    }

    /**
     * Menyiapkan konteks reduksi Barrett untuk modulus tertentu.
     *
     * Reduksi modular dengan bcmod() sangat lambat (~10x bcmul). Barrett
     * menggantikannya dengan dua perkalian dan pemotongan digit desimal
     * (pembagian dengan 10^k = membuang k digit terakhir).
     *
     * @return array{n: string, k: int, mu: string}
     */
    public static function barrettContext(string $modulus): array
    {
        $k = strlen($modulus);

        return [
            'n' => $modulus,
            'k' => $k,
            'mu' => bcdiv('1' . str_repeat('0', 2 * $k), $modulus, 0),
        ];
    }

    /**
     * x mod n untuk 0 <= x < n^2 memakai konteks Barrett.
     *
     * @param  array{n: string, k: int, mu: string}  $ctx
     */
    public static function barrettReduce(string $x, array $ctx): string
    {
        $k = $ctx['k'];
        $n = $ctx['n'];

        $len = strlen($x) - ($k - 1);
        $q1 = $len > 0 ? substr($x, 0, $len) : '0';

        $q2 = bcmul($q1, $ctx['mu'], 0);
        $len = strlen($q2) - ($k + 1);
        $q3 = $len > 0 ? substr($q2, 0, $len) : '0';

        $r = bcsub($x, bcmul($q3, $n, 0), 0);
        while (bccomp($r, $n, 0) >= 0) {
            $r = bcsub($r, $n, 0);
        }

        return $r;
    }

    /** (base ^ exponent) mod modulus dengan metode square-and-multiply. */
    public static function powMod(string $base, string $exponent, string $modulus, ?array $ctx = null): string
    {
        $ctx ??= self::barrettContext($modulus);

        $result = bcmod('1', $modulus, 0);
        $base = bcmod($base, $modulus, 0);
        $exponentBytes = self::toBytes($exponent);
        $started = false;

        for ($i = 0, $n = strlen($exponentBytes); $i < $n; $i++) {
            $byte = ord($exponentBytes[$i]);

            for ($bit = 7; $bit >= 0; $bit--) {
                if ($started) {
                    $result = self::barrettReduce(bcmul($result, $result, 0), $ctx);
                }

                if (($byte >> $bit) & 1) {
                    $result = self::barrettReduce(bcmul($result, $base, 0), $ctx);
                    $started = true;
                }
            }
        }

        return $result;
    }

    /** FPB dengan algoritma Euclid. */
    public static function gcd(string $a, string $b): string
    {
        while (bccomp($b, '0', 0) !== 0) {
            [$a, $b] = [$b, bcmod($a, $b, 0)];
        }

        return $a;
    }

    /** Invers modular a^-1 mod m dengan Extended Euclid. Mengembalikan null bila tidak ada. */
    public static function modInverse(string $a, string $m): ?string
    {
        $oldR = bcmod($a, $m, 0);
        $r = $m;
        $oldS = '1';
        $s = '0';

        while (bccomp($r, '0', 0) !== 0) {
            $q = bcdiv($oldR, $r, 0);

            [$oldR, $r] = [$r, bcsub($oldR, bcmul($q, $r, 0), 0)];
            [$oldS, $s] = [$s, bcsub($oldS, bcmul($q, $s, 0), 0)];
        }

        if ($oldR !== '1') {
            return null;
        }

        $inverse = bcmod($oldS, $m, 0);
        if (bccomp($inverse, '0', 0) < 0) {
            $inverse = bcadd($inverse, $m, 0);
        }

        return $inverse;
    }

    /** Uji keprimaan Miller-Rabin dengan basis bilangan prima kecil. */
    public static function isProbablePrime(string $n, int $rounds = 8, bool $trialDivision = true): bool
    {
        if (bccomp($n, '2', 0) < 0) {
            return false;
        }

        if ($trialDivision || strlen($n) < 6) {
            foreach (self::smallPrimes() as $p) {
                if (bccomp($n, (string) $p, 0) === 0) {
                    return true;
                }
                if (bcmod($n, (string) $p, 0) === '0') {
                    return false;
                }
            }
        }

        $ctx = self::barrettContext($n);

        // n - 1 = d * 2^s dengan d ganjil
        $nMinusOne = bcsub($n, '1', 0);
        $d = $nMinusOne;
        $s = 0;
        while (bcmod($d, '2', 0) === '0') {
            $d = bcdiv($d, '2', 0);
            $s++;
        }

        $bases = array_slice(self::smallPrimes(), 0, $rounds);

        foreach ($bases as $a) {
            $x = self::powMod((string) $a, $d, $n, $ctx);

            if ($x === '1' || $x === $nMinusOne) {
                continue;
            }

            $composite = true;
            for ($r = 1; $r < $s; $r++) {
                $x = self::barrettReduce(bcmul($x, $x, 0), $ctx);
                if ($x === $nMinusOne) {
                    $composite = false;
                    break;
                }
            }

            if ($composite) {
                return false;
            }
        }

        return true;
    }

    /**
     * Membangkitkan bilangan prima acak $bits bit (kelipatan 8) dengan p = 3 (mod 4).
     * Dua bit teratas selalu 1 sehingga hasil kali dua prima tepat 2*$bits bit.
     */
    public static function randomPrime(int $bits): string
    {
        if ($bits < 16 || $bits % 8 !== 0) {
            throw new InvalidArgumentException('Jumlah bit harus kelipatan 8 dan minimal 16.');
        }

        $byteLength = intdiv($bits, 8);
        $limit = bcpow('2', (string) $bits, 0);
        $small = self::smallPrimes();

        while (true) {
            $raw = random_bytes($byteLength);
            $raw[0] = chr(ord($raw[0]) | 0xC0);
            $raw[$byteLength - 1] = chr(ord($raw[$byteLength - 1]) | 0x03);

            $start = self::fromBytes($raw);

            // Sieve bertahap: sisa pembagian dihitung sekali, lalu diperbarui dengan integer native.
            $residues = [];
            foreach ($small as $p) {
                $residues[] = (int) bcmod($start, (string) $p, 0);
            }

            for ($k = 0; $k < 5000; $k++) {
                $offset = 4 * $k;
                $divisible = false;

                foreach ($small as $i => $p) {
                    if (($residues[$i] + $offset) % $p === 0) {
                        $divisible = true;
                        break;
                    }
                }

                if ($divisible) {
                    continue;
                }

                $candidate = bcadd($start, (string) $offset, 0);
                if (bccomp($candidate, $limit, 0) >= 0) {
                    break;
                }

                if (self::isProbablePrime($candidate, 8, false)) {
                    return $candidate;
                }
            }
        }
    }

    /** @return int[] bilangan prima < 2000 */
    private static function smallPrimes(): array
    {
        if (self::$smallPrimes !== null) {
            return self::$smallPrimes;
        }

        $primes = [];
        for ($n = 2; $n < 2000; $n++) {
            $isPrime = true;
            for ($d = 2; $d * $d <= $n; $d++) {
                if ($n % $d === 0) {
                    $isPrime = false;
                    break;
                }
            }
            if ($isPrime) {
                $primes[] = $n;
            }
        }

        return self::$smallPrimes = $primes;
    }
}
