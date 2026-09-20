<?php

namespace App\Bot\Engines;

use App\Bot\Contracts\BotAdapter;
use App\Models\Bot;
use App\Models\Setting;
use App\Models\Submission;
use Illuminate\Support\Facades\Log;

class NotificationEngine
{
    public function notifyAdmin(Submission $submission, Bot $bot, BotAdapter $adapter): void
    {
        $adminChatId = Setting::get('admin_telegram_chat_id', config('bot.admin_telegram_chat_id'));

        if (! $adminChatId) {
            Log::info('NotificationEngine: admin_telegram_chat_id not configured, skipping notification.', [
                'submission_id' => $submission->id,
            ]);

            return;
        }

        $submission->loadMissing(['process', 'values.field.step']);

        $answers = $submission->values
            ->sortBy([
                fn ($value) => $value->field?->step?->display_order ?? 0,
                fn ($value) => $value->field?->display_order ?? 0,
            ])
            ->map(fn ($value) => "{$value->field?->label}: {$value->value}")
            ->implode("\n");

        $text = implode("\n", [
            '✅ فرآیند جدید تکمیل شد',
            '',
            "فرآیند: {$submission->process?->name}",
            "پلتفرم: {$submission->platform}",
            'کاربر: @'.($submission->username ?? '-'),
            'شناسه: #'.substr($submission->id, 0, 8),
            "زمان: {$submission->completed_at}",
            '',
            '--- پاسخ‌ها ---',
            $answers,
        ]);

        $adapter->sendMessage($adminChatId, $text);
    }
}
