<?php

namespace App\Bot;

use App\Bot\AdapterFactory;
use App\Bot\Contracts\BotAdapter;
use App\Bot\DTOs\IncomingMessage;
use App\Bot\Engines\FormEngine;
use App\Bot\Engines\SessionEngine;
use App\Models\Bot;
use App\Models\Process;
use App\Models\Submission;
use Illuminate\Support\Str;

class MessageDispatcher
{
    public function __construct(
        protected SessionEngine $sessionEngine,
        protected FormEngine $formEngine,
    ) {
    }

    public function dispatch(IncomingMessage $msg, Bot $bot, BotAdapter $adapter): void
    {
        $adminAction = $this->extractAdminAction($msg);

        if ($adminAction !== null) {
            $this->handleAdminAction($adminAction['action'], $adminAction['submissionId'], $msg, $adapter);

            return;
        }

        $process = $this->extractProcessSelection($msg, $bot);

        if ($process !== null) {
            $session = $this->sessionEngine->create($bot, $msg, $process);

            $field = $session->currentField;

            if (! $field) {
                $adapter->sendMessage($msg->chatId, 'این فرآیند هیچ مرحله‌ای ندارد.');

                return;
            }

            $this->formEngine->sendField($msg->chatId, $field, $adapter);

            return;
        }

        if ($msg->text === '/start') {
            $this->formEngine->showMainMenu($msg->chatId, $bot, $adapter);

            return;
        }

        $this->formEngine->handleMessage($msg, $bot, $adapter);
    }

    protected function extractProcessSelection(IncomingMessage $msg, Bot $bot): ?Process
    {
        $processes = $bot->processes()
            ->where('is_current_version', true)
            ->where('is_active', true);

        if ($msg->platform === 'rubika') {
            if ($msg->type !== 'text') {
                return null;
            }

            return $processes->where('name', $msg->text)->first();
        }

        if ($msg->type !== 'callback') {
            return null;
        }

        if (! Str::startsWith($msg->text, 'process:')) {
            return null;
        }

        $processId = Str::after($msg->text, 'process:');

        return $processes->where('processes.id', $processId)->first();
    }

    protected function extractAdminAction(IncomingMessage $msg): ?array
    {
        if ($msg->type !== 'callback') {
            return null;
        }

        foreach (['approve', 'reject'] as $action) {
            if (Str::startsWith($msg->text, "{$action}:")) {
                return [
                    'action' => $action,
                    'submissionId' => Str::after($msg->text, "{$action}:"),
                ];
            }
        }

        return null;
    }

    protected function handleAdminAction(string $action, string $submissionId, IncomingMessage $msg, BotAdapter $adapter): void
    {
        $submission = Submission::find($submissionId);

        if (! $submission) {
            $adapter->sendMessage($msg->chatId, 'این درخواست پیدا نشد.');

            return;
        }

        if ($submission->admin_action !== 'pending') {
            $adapter->sendMessage($msg->chatId, 'این درخواست قبلاً پردازش شده است.');

            return;
        }

        $submission->update(['admin_action' => $action === 'approve' ? 'approved' : 'rejected']);

        $targetBot = Bot::find($submission->bot_id);

        if ($targetBot) {
            $targetAdapter = AdapterFactory::make($targetBot);

            $userText = $action === 'approve'
                ? "✅ درخواست شما با موفقیت تأیید شد.\nمتشکریم."
                : "❌ متأسفانه درخواست شما رد شد.\nبرای اطلاعات بیشتر با ما تماس بگیرید.";

            $targetAdapter->sendMessage($submission->external_user_id, $userText);
        }

        $shortId = substr($submission->id, 0, 8);
        $adminText = $action === 'approve' ? "✅ تأیید شد - {$shortId}" : "❌ رد شد - {$shortId}";

        if ($msg->messageId) {
            $adapter->editMessage($msg->chatId, $msg->messageId, $adminText);
        }
    }
}
