<?php

namespace App\Bot\Adapters;

use App\Bot\Contracts\BotAdapter;
use App\Bot\DTOs\IncomingMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramAdapter implements BotAdapter
{
    protected string $baseUrl;

    protected ?array $lastPayload = null;

    public function __construct(protected string $token)
    {
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    public function sendMessage(string $chatId, string $text): void
    {
        $this->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    public function sendKeyboard(string $chatId, string $text, array $buttons): void
    {
        $keyboard = array_map(
            fn (array $button) => [[
                'text' => $button['label'],
                'callback_data' => $button['value'],
            ]],
            $buttons,
        );

        $this->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    public function getUser(string $chatId): array
    {
        $from = $this->lastPayload['message']['from']
            ?? $this->lastPayload['callback_query']['from']
            ?? [];

        return [
            'id' => (string) ($from['id'] ?? $chatId),
            'username' => $from['username'] ?? null,
            'name' => trim(($from['first_name'] ?? '').' '.($from['last_name'] ?? '')) ?: null,
        ];
    }

    public function setWebhook(string $url): bool
    {
        $response = $this->call('setWebhook', ['url' => $url]);

        return (bool) ($response['ok'] ?? false);
    }

    public function parseIncoming(array $payload): IncomingMessage
    {
        $this->lastPayload = $payload;

        if (isset($payload['callback_query'])) {
            $callback = $payload['callback_query'];
            $from = $callback['from'] ?? [];
            $chatId = (string) ($callback['message']['chat']['id'] ?? $from['id'] ?? '');

            return new IncomingMessage(
                chatId: $chatId,
                userId: (string) ($from['id'] ?? $chatId),
                username: $from['username'] ?? null,
                text: (string) ($callback['data'] ?? ''),
                type: 'callback',
                platform: 'telegram',
            );
        }

        $message = $payload['message'] ?? [];
        $from = $message['from'] ?? [];
        $chatId = (string) ($message['chat']['id'] ?? $from['id'] ?? '');

        return new IncomingMessage(
            chatId: $chatId,
            userId: (string) ($from['id'] ?? $chatId),
            username: $from['username'] ?? null,
            text: (string) ($message['text'] ?? ''),
            type: 'text',
            platform: 'telegram',
        );
    }

    protected function call(string $method, array $params): array
    {
        try {
            $response = Http::timeout(10)->asForm()->post("{$this->baseUrl}{$method}", $params);

            if (! $response->successful()) {
                Log::warning("Telegram API call failed: {$method}", [
                    'params' => $params,
                    'response' => $response->body(),
                ]);
            }

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("Telegram API call exception: {$method}", [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
