<?php

namespace App\Bot\DTOs;

class ValidationResult
{
    public function __construct(
        public bool $valid,
        public ?string $errorMessage = null,
    ) {
    }

    public static function ok(): self
    {
        return new self(true);
    }

    public static function fail(string $errorMessage): self
    {
        return new self(false, $errorMessage);
    }
}
