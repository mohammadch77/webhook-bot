<?php

namespace App\Bot\Adapters;

use App\Bot\Contracts\BotAdapter;
use App\Bot\DTOs\IncomingMessage;

/**
 * In-memory adapter used for manual/automated testing of the Bot engines
 * without hitting a real platform API.
 */
class FakeAdapter implements BotAdapter
{
    public array $sentMessages = [];

    public array $sentKeyboards = [];

    public array $editedMessages = [];

    public function sendMessage(string $chatId, string $text): void
    {
        $this->sentMessages[] = ['chatId' => $chatId, 'text' => $text];
    }

    public function editMessage(string $chatId, string $messageId, string $text): void
    {
        $this->editedMessages[] = ['chatId' => $chatId, 'messageId' => $messageId, 'text' => $text];
    }

    public function sendKeyboard(string $chatId, string $text, array $buttons): void
    {
        $this->sentKeyboards[] = ['chatId' => $chatId, 'text' => $text, 'buttons' => $buttons];
    }

    public function getUser(string $chatId): array
    {
        return ['id' => $chatId, 'username' => null, 'name' => null];
    }

    public function setWebhook(string $url): bool
    {
        return true;
    }

    public function parseIncoming(array $payload): IncomingMessage
    {
        return new IncomingMessage(
            chatId: (string) $payload['chatId'],
            userId: (string) $payload['chatId'],
            username: $payload['username'] ?? null,
            text: (string) $payload['text'],
            type: $payload['type'] ?? 'text',
            platform: 'telegram',
            messageId: isset($payload['messageId']) ? (string) $payload['messageId'] : null,
        );
    }
}
