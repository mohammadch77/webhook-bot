<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Index', [
            'settings' => [
                'admin_telegram_chat_id' => Setting::get('admin_telegram_chat_id', config('bot.admin_telegram_chat_id')),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admin_telegram_chat_id' => ['nullable', 'string', 'max:100'],
        ]);

        Setting::set('admin_telegram_chat_id', $validated['admin_telegram_chat_id'] ?? null);

        return back()->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }
}
