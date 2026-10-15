<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    public function __construct(public readonly array $errors, public readonly array $old = [])
    {
        parent::__construct('The given data was invalid.');
    }
}
