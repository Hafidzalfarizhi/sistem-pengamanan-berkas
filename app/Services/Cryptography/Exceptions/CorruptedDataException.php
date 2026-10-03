<?php

namespace App\Services\Cryptography\Exceptions;

use RuntimeException;

/**
 * Dilempar ketika struktur atau isi file terenkripsi rusak / tidak valid.
 */
class CorruptedDataException extends RuntimeException
{
}
