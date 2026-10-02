<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Format
{
    /** 2578432 => "2.46 MB" */
    public static function bytes(int|string|null $bytes): string
    {
        $bytes = (float) ($bytes ?? 0);

        if ($bytes < 1024) {
            return number_format($bytes, 0, '.', '') . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $i = -1;
        do {
            $bytes /= 1024;
            $i++;
        } while ($bytes >= 1024 && $i < count($units) - 1);

        return number_format($bytes, 2, '.', '') . ' ' . $units[$i];
    }

    /** "28 Sep 2026, 19:32:15" */
    public static function dateTime(?CarbonInterface $date): string
    {
        return $date ? $date->translatedFormat('d M Y, H:i:s') : '-';
    }

    /** "28 September 2026" */
    public static function dateLong(?CarbonInterface $date): string
    {
        return $date ? $date->translatedFormat('d F Y') : '-';
    }

    /** "19:32:15 WIB" */
    public static function timeWib(?CarbonInterface $date): string
    {
        return $date ? $date->format('H:i:s') . ' WIB' : '-';
    }

    /** Membersihkan nama file dari path dan karakter kontrol. */
    public static function safeFileName(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        $name = basename($name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        $name = trim($name);

        return $name === '' || $name === '.' || $name === '..' ? 'file' : mb_strcut($name, 0, 200, 'UTF-8');
    }
}
