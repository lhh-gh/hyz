<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class BusinessException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $bizCode = 4000,
    ) {
        parent::__construct($message, $bizCode);
    }

    public function bizCode(): int
    {
        return $this->bizCode;
    }
}