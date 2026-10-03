<?php

namespace App\Services\Cryptography\Exceptions;

use RuntimeException;

/**
 * Dilempar ketika password/kunci yang dimasukkan tidak cocok dengan file.
 */
class InvalidKeyException extends RuntimeException
{
}
