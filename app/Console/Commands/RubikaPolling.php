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
    protected $signature = 'rubika:poll';

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

            sleep(5);
        }
    }

    protected function pollBot(Bot $bot, MessageDispatcher $dispatcher): void
    {
        $cacheKey = "rubika_offset_{$bot->id}";

        try {
            $offsetId = Cache::get($cacheKey, '');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(
                "https://botapi.rubika.ir/v3/{$bot->token}/getUpdates",
                ['offset_id' => $offsetId, 'limit' => 100]
            );

            $data = $response->json();

            if (! isset($data['status']) || strtoupper($data['status']) !== 'OK') {
                return;
            }

            $updates = $data['data']['updates'] ?? [];

            if (empty($updates)) {
                return;
            }

            $adapter = AdapterFactory::make($bot);
            $lastUpdateId = $offsetId;

            foreach ($updates as $update) {
                try {
                    $msg = $adapter->parseIncoming($update);
                    $dispatcher->dispatch($msg, $bot, $adapter);
                } catch (\Throwable $e) {
                    Log::error('Rubika polling: failed to process update', [
                        'bot_id' => $bot->id,
                        'update' => $update,
                        'error' => $e->getMessage(),
                    ]);
                }

                if (isset($update['update_id'])) {
                    $lastUpdateId = (string) $update['update_id'];
                }
            }

            Cache::put($cacheKey, $lastUpdateId);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'TOO_REQUESTS')) {
                Log::warning('Rubika rate limited, waiting 30s');
                sleep(30);

                return;
            }

            Log::error('Rubika polling: failed to poll bot', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
