<?php

namespace App\Bot\DTOs;

class IncomingMessage
{
    public function __construct(
        public string $chatId,
        public string $userId,
        public ?string $username,
        public string $text,
        public string $type,
        public string $platform,
    ) {
    }
}
