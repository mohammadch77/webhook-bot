<?php

namespace App\Bot\Adapters;

use App\Bot\Contracts\BotAdapter;
use App\Bot\DTOs\IncomingMessage;
use Illuminate\Support\Facades\Http;
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

    public function editMessage(string $chatId, string $messageId, string $text): void
    {
        $this->call(fn () => $this->client->chatId($chatId)->messageId($messageId)->text($text)->editMessageText());
        $this->call(fn () => $this->client->chatId($chatId)->messageId($messageId)->inlineKeypad(new Keypad([]))->editMessageKeypad());
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
            Log::info('Rubika setWebhook sending', [
                'url_to_set' => $url,
                'token_length' => strlen($this->token),
                'endpoint' => "https://botapi.rubika.ir/v3/{$this->token}/updateBotEndpoints",
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(
                "https://botapi.rubika.ir/v3/{$this->token}/updateBotEndpoints",
                ['url' => $url, 'type' => 'ReceiveUpdate']
            );

            $data = $response->json();
            Log::info('Rubika setWebhook direct response', ['data' => $data]);

            return isset($data['status']) && strtoupper($data['status']) === 'OK';
        } catch (\Exception $e) {
            Log::error('Rubika setWebhook error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function parseIncoming(array $payload): IncomingMessage
    {
        $update = $payload['update'] ?? $payload;
        $this->lastPayload = $update;

        // TODO: real payload shape for callback buttons (type = "CallbackQuery") not confirmed yet.
        $message = $update['new_message'] ?? [];
        $chatId = (string) ($update['chat_id'] ?? '');

        return new IncomingMessage(
            chatId: $chatId,
            userId: (string) ($message['sender_id'] ?? $chatId),
            username: null,
            text: (string) ($message['text'] ?? ''),
            type: 'text',
            platform: 'rubika',
            messageId: isset($message['message_id']) ? (string) $message['message_id'] : null,
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
