<?php

namespace App\Bot\Engines;

use App\Bot\AdapterFactory;
use App\Models\Bot;
use App\Models\Setting;
use App\Models\Submission;
use Illuminate\Support\Facades\Log;

class NotificationEngine
{
    protected const PLATFORMS = ['telegram', 'bale', 'rubika'];

    public function notifyAdmin(Submission $submission): void
    {
        $platform = $submission->platform;

        if (! in_array($platform, self::PLATFORMS, true)) {
            return;
        }

        $chatId = Setting::get("admin_{$platform}_chat_id", config("bot.admin_{$platform}_chat_id"));

        if (! $chatId) {
            Log::info("NotificationEngine: admin_{$platform}_chat_id not configured, skipping notification.", [
                'submission_id' => $submission->id,
                'platform' => $platform,
            ]);

            return;
        }

        $bot = Bot::where('platform', $platform)->where('status', 'active')->first()
            ?? Bot::where('platform', $platform)->first();

        if (! $bot) {
            Log::info("NotificationEngine: no bot configured for platform [{$platform}], skipping notification.", [
                'submission_id' => $submission->id,
            ]);

            return;
        }

        try {
            $adapter = AdapterFactory::make($bot);
            $text = $this->buildMessage($submission);

            $adapter->sendKeyboard($chatId, $text, [
                ['value' => "approve:{$submission->id}", 'label' => '✅ تأیید'],
                ['value' => "reject:{$submission->id}", 'label' => '❌ رد'],
            ]);
        } catch (\Throwable $e) {
            Log::error("NotificationEngine: failed to notify admin on platform [{$platform}].", [
                'submission_id' => $submission->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function buildMessage(Submission $submission): string
    {
        $submission->loadMissing(['process', 'values.field.step']);

        $answers = $submission->values
            ->sortBy([
                fn ($value) => $value->field?->step?->display_order ?? 0,
                fn ($value) => $value->field?->display_order ?? 0,
            ])
            ->map(fn ($value) => "{$value->field?->label}: {$value->value}")
            ->implode("\n");

        return implode("\n", [
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
    }
}
