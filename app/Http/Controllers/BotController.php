<?php

namespace App\Http\Controllers;

use App\Bot\AdapterFactory;
use App\Http\Requests\StoreBotRequest;
use App\Http\Requests\UpdateBotRequest;
use App\Models\Bot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            return back()->with('error', 'اتصال روبیکا در دست توسعه است.');
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

    public function setWebhook(Bot $bot): RedirectResponse
    {
        if ($bot->platform !== 'telegram') {
            return back()->with('error', 'تنظیم Webhook فعلاً فقط برای تلگرام پشتیبانی می‌شود.');
        }

        $url = url("/webhook/telegram/{$bot->id}");

        try {
            $adapter = AdapterFactory::make($bot);
            $success = $adapter->setWebhook($url);
        } catch (\Throwable $e) {
            return back()->with('error', 'تنظیم Webhook ناموفق بود: '.$e->getMessage());
        }

        if (! $success) {
            return back()->with('error', 'تلگرام درخواست تنظیم Webhook را رد کرد.');
        }

        $bot->update(['webhook_url' => $url]);

        return back()->with('success', "Webhook با موفقیت روی {$url} تنظیم شد.");
    }
}
