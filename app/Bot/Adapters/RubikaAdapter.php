<?php

namespace App\Bot\Adapters;

use App\Bot\Contracts\BotAdapter;
use App\Bot\DTOs\IncomingMessage;
use Illuminate\Support\Facades\Log;
use RubikaPhp\Core\Bot;
use RubikaPhp\Enums\ButtonTypeEnum;
use RubikaPhp\Models\Button;
use RubikaPhp\Models\Keypad;
use RubikaPhp\Models\KeypadRow;

class RubikaAdapter implements BotAdapter
{
    protected Bot $client;

    protected ?array $lastPayload = null;

    public function __construct(protected string $token)
    {
        $this->client = new Bot($this->token);
    }

    public function sendMessage(string $chatId, string $text): void
    {
        $this->call(fn () => $this->client->chatId($chatId)->text($text)->sendMessage());
    }

    public function sendKeyboard(string $chatId, string $text, array $buttons): void
    {
        $rows = array_map(
            fn (array $button) => new KeypadRow([
                new Button($button['value'], ButtonTypeEnum::SIMPLE, $button['label']),
            ]),
            $buttons,
        );

        $keypad = new Keypad($rows);

        $this->call(fn () => $this->client->chatId($chatId)->text($text)->inlineKeypad($keypad)->sendMessage());
    }

    public function getUser(string $chatId): array
    {
        $message = $this->lastPayload['new_message']
            ?? $this->lastPayload['inline_message']
            ?? [];

        return [
            'id' => (string) ($message['sender_id'] ?? $chatId),
            'username' => null,
            'name' => null,
        ];
    }

    public function setWebhook(string $url): bool
    {
        try {
            $response = $this->client->updateBotEndpoints($url, 'ReceiveUpdate');

            return ($response['status'] ?? null) === 'OK';
        } catch (\Throwable $e) {
            Log::error('Rubika API call exception: updateBotEndpoints', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function parseIncoming(array $payload): IncomingMessage
    {
        $update = $payload['update'] ?? $payload;
        $this->lastPayload = $update;

        if (isset($update['inline_message'])) {
            $inline = $update['inline_message'];

            return new IncomingMessage(
                chatId: (string) ($inline['chat_id'] ?? ''),
                userId: (string) ($inline['sender_id'] ?? ''),
                username: null,
                text: (string) ($inline['aux_data']['button_id'] ?? ''),
                type: 'callback',
                platform: 'rubika',
            );
        }

        $message = $update['new_message'] ?? [];
        $chatId = (string) ($update['chat_id'] ?? '');

        return new IncomingMessage(
            chatId: $chatId,
            userId: (string) ($message['sender_id'] ?? $chatId),
            username: null,
            text: (string) ($message['text'] ?? ''),
            type: 'text',
            platform: 'rubika',
        );
    }

    protected function call(callable $request): array
    {
        try {
            return $request();
        } catch (\Throwable $e) {
            Log::error('Rubika API call exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
