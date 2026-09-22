<?php

namespace App\Console\Commands;

use App\Bot\AdapterFactory;
use App\Bot\MessageDispatcher;
use App\Models\Bot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RubikaPolling extends Command
{
    protected $signature = 'bot:rubika';

    protected $description = 'Poll Rubika bot for new messages';

    public function handle(MessageDispatcher $dispatcher): int
    {
        $this->info('Starting Rubika polling...');

        while (true) {
            $bots = Bot::query()
                ->where('platform', 'rubika')
                ->where('status', 'active')
                ->get();

            foreach ($bots as $bot) {
                $this->pollBot($bot, $dispatcher);
            }

            sleep(10);
        }
    }

    protected function pollBot(Bot $bot, MessageDispatcher $dispatcher): void
    {
        $cacheKey = "rubika_offset_{$bot->id}";

        try {
            Log::info('Rubika polling: starting pollBot', ['bot' => $bot->name]);

            $offsetId = Cache::get($cacheKey, '');

            Log::info('Rubika polling: offset read', ['bot' => $bot->name, 'offset_id' => $offsetId]);

            Log::info('Rubika polling: checking updates', ['bot' => $bot->name]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(
                "https://botapi.rubika.ir/v3/{$bot->token}/getUpdates",
                ['offset_id' => $offsetId, 'limit' => 100]
            );

            Log::info('Rubika polling: getUpdates responded', ['bot' => $bot->name, 'status_code' => $response->status()]);

            $data = $response->json();
            $status = strtoupper($data['status'] ?? '');

            Log::info('Rubika polling: response parsed', ['bot' => $bot->name, 'status' => $status]);

            if (str_contains($status, 'TOO_REQUESTS') || str_contains((string) $response->body(), 'TOO_REQUESTS')) {
                Log::warning('Rubika rate limited, waiting 60s');
                sleep(60);

                return;
            }

            if ($status !== 'OK') {
                Log::warning('Rubika polling: non-OK status, skipping', ['bot' => $bot->name, 'status' => $status]);

                return;
            }

            $updates = $data['data']['updates'] ?? [];

            Log::info('Rubika polling: updates extracted', ['bot' => $bot->name, 'count' => count($updates)]);

            if (empty($updates)) {
                return;
            }

            $adapter = AdapterFactory::make($bot);

            Log::info('Rubika polling: adapter created, processing updates', ['bot' => $bot->name]);

            foreach ($updates as $update) {
                try {
                    Log::info('Rubika polling: parsing update', ['bot' => $bot->name, 'update_id' => $update['update_id'] ?? null]);

                    $msg = $adapter->parseIncoming($update);

                    Log::info('Rubika polling: dispatching message', ['bot' => $bot->name, 'chat_id' => $msg->chatId]);

                    $dispatcher->dispatch($msg, $bot, $adapter);

                    Log::info('Rubika polling: dispatched successfully', ['bot' => $bot->name, 'update_id' => $update['update_id'] ?? null]);
                } catch (\Throwable $e) {
                    Log::error('Rubika polling: failed to process update', [
                        'bot_id' => $bot->id,
                        'update' => $update,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $nextOffset = $data['data']['next_offset_id'] ?? '';

            if ($nextOffset) {
                Log::info('Rubika polling: saving offset', ['bot' => $bot->name, 'offset_id' => $nextOffset]);

                Cache::put($cacheKey, $nextOffset);
            }

            Log::info('Rubika polling: pollBot finished', ['bot' => $bot->name]);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'TOO_REQUESTS')) {
                Log::warning('Rubika rate limited, waiting 60s');
                sleep(60);

                return;
            }

            Log::error('Rubika polling: failed to poll bot', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
