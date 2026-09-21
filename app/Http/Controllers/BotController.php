<?php

namespace App\Http\Controllers;

use App\Bot\AdapterFactory;
use App\Http\Requests\StoreBotRequest;
use App\Http\Requests\UpdateBotRequest;
use App\Models\Bot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BotController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Bots/Index', [
            'bots' => Bot::orderByDesc('created_at')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Bots/Form', [
            'bot' => null,
        ]);
    }

    public function store(StoreBotRequest $request): RedirectResponse
    {
        Bot::create([
            ...$request->validated(),
            'status' => 'inactive',
        ]);

        return redirect()->route('bots.index')->with('success', 'ربات با موفقیت ساخته شد.');
    }

    public function edit(Bot $bot): Response
    {
        return Inertia::render('Bots/Form', [
            'bot' => $bot,
        ]);
    }

    public function update(UpdateBotRequest $request, Bot $bot): RedirectResponse
    {
        $bot->update($request->validated());

        return redirect()->route('bots.index')->with('success', 'ربات با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Bot $bot): RedirectResponse
    {
        if ($bot->processPlatforms()->exists()) {
            return back()->with('error', 'این ربات به یک یا چند فرآیند متصل است و قابل حذف نیست.');
        }

        $bot->delete();

        return back()->with('success', 'ربات حذف شد.');
    }

    public function testConnection(Request $request, Bot $bot): RedirectResponse
    {
        if ($bot->platform === 'rubika') {
            try {
                $response = Http::timeout(10)
                    ->withBody('{}', 'application/json')
                    ->post("https://botapi.rubika.ir/v3/{$bot->token}/getMe");
            } catch (\Throwable $e) {
                return back()->with('error', 'اتصال برقرار نشد: '.$e->getMessage());
            }

            $data = $response->json();

            Log::info('Rubika getMe response', [
                'status_code' => $response->status(),
                'body' => $response->body(),
                'json' => $data,
            ]);

            $status = strtoupper((string) ($data['status'] ?? ''));

            if (! $response->successful() || $status !== 'OK') {
                return back()->with('error', 'توکن نامعتبر است یا اتصال ناموفق بود.');
            }

            $botName = $data['data']['bot']['bot_title'] ?? null;

            $bot->update([
                'status' => 'active',
                'webhook_url' => url("/webhook/{$bot->platform}/{$bot->id}"),
            ]);

            return back()->with('success', "اتصال موفق بود. نام ربات: {$botName}");
        }

        $baseUrl = match ($bot->platform) {
            'telegram' => 'https://api.telegram.org',
            'bale' => 'https://tapi.bale.ai',
        };

        try {
            $response = Http::timeout(10)->get("{$baseUrl}/bot{$bot->token}/getMe");
        } catch (\Throwable $e) {
            return back()->with('error', 'اتصال برقرار نشد: '.$e->getMessage());
        }

        $data = $response->json();

        if (! $response->successful() || ! ($data['ok'] ?? false)) {
            return back()->with('error', 'توکن نامعتبر است یا اتصال ناموفق بود.');
        }

        $botName = $data['result']['username'] ?? $data['result']['first_name'] ?? null;

        $bot->update([
            'status' => 'active',
            'webhook_url' => url("/webhook/{$bot->platform}/{$bot->id}"),
        ]);

        return back()->with('success', "اتصال موفق بود. نام ربات: {$botName}");
    }

    public function setWebhook(Request $request, Bot $bot): RedirectResponse
    {
        if (! in_array($bot->platform, ['telegram', 'bale', 'rubika'], true)) {
            return back()->with('error', 'تنظیم Webhook برای این پلتفرم پشتیبانی نمی‌شود.');
        }

        $baseUrl = env('APP_URL') ?: $request->getSchemeAndHttpHost();
        $url = rtrim($baseUrl, '/')."/webhook/{$bot->platform}/{$bot->id}";

        try {
            $adapter = AdapterFactory::make($bot);
            $success = $adapter->setWebhook($url);
        } catch (\Throwable $e) {
            return back()->with('error', 'تنظیم Webhook ناموفق بود: '.$e->getMessage());
        }

        if (! $success) {
            return back()->with('error', 'درخواست تنظیم Webhook رد شد.');
        }

        $bot->update(['webhook_url' => $url]);

        return back()->with('success', "Webhook با موفقیت روی {$url} تنظیم شد.");
    }
}
