<?php

namespace App\Bot;

use App\Bot\Adapters\TelegramAdapter;
use App\Bot\Contracts\BotAdapter;
use App\Models\Bot;
use InvalidArgumentException;

class AdapterFactory
{
    public static function make(Bot $bot): BotAdapter
    {
        return match ($bot->platform) {
            'telegram' => new TelegramAdapter($bot->token),
            default => throw new InvalidArgumentException("No adapter available for platform [{$bot->platform}]."),
        };
    }
}
