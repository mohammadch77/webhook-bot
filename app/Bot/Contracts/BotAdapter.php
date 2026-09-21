<?php

namespace App\Bot\Contracts;

use App\Bot\DTOs\IncomingMessage;

interface BotAdapter
{
    public function sendMessage(string $chatId, string $text): void;

    /**
     * Edit an already-sent message's text and strip its inline keyboard.
     */
    public function editMessage(string $chatId, string $messageId, string $text): void;

    /**
     * @param  array<int, array{value: string, label: string}>  $buttons
     */
    public function sendKeyboard(string $chatId, string $text, array $buttons): void;

    /**
     * @return array{id: string, username: ?string, name: ?string}
     */
    public function getUser(string $chatId): array;

    public function setWebhook(string $url): bool;

    public function parseIncoming(array $payload): IncomingMessage;
}
