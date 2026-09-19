<?php

namespace App\Bot\DTOs;

class ConditionResult
{
    public function __construct(
        public string $action,
        public ?string $targetStepId = null,
        public ?string $stopMessage = null,
    ) {
    }
}
