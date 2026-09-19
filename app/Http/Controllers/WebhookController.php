<?php

namespace App\Http\Controllers;

use App\Bot\AdapterFactory;
use App\Bot\MessageDispatcher;
use App\Models\Bot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __invoke(Request $request, string $platform, string $botId, MessageDispatcher $dispatcher): JsonResponse
    {
        try {
            $bot = Bot::where('id', $botId)->where('platform', $platform)->first();

            if (! $bot) {
                Log::warning('Webhook received for unknown bot', [
                    'platform' => $platform,
                    'bot_id' => $botId,
                ]);

                return response()->json(['ok' => true]);
            }

            $adapter = AdapterFactory::make($bot);
            $message = $adapter->parseIncoming($request->all());

            $dispatcher->dispatch($message, $bot, $adapter);
        } catch (\Throwable $e) {
            Log::error('Webhook processing failed', [
                'platform' => $platform,
                'bot_id' => $botId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
