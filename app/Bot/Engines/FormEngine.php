<?php

namespace App\Bot\Engines;

use App\Bot\Contracts\BotAdapter;
use App\Bot\DTOs\IncomingMessage;
use App\Models\Bot;
use App\Models\Process;
use App\Models\ProcessField;
use App\Models\ProcessStep;

class FormEngine
{
    public function __construct(
        protected SessionEngine $sessionEngine,
        protected ValidationEngine $validationEngine,
        protected NotificationEngine $notificationEngine,
    ) {
    }

    public function handleMessage(IncomingMessage $msg, Bot $bot, BotAdapter $adapter): void
    {
        $session = $this->sessionEngine->getOrCreate($msg, $bot);

        if (! $session) {
            $this->showMainMenu($msg->chatId, $bot, $adapter);

            return;
        }

        if ($msg->text === '/cancel') {
            $this->sessionEngine->expire($session);
            $adapter->sendMessage($msg->chatId, 'فرآیند لغو شد.');

            return;
        }

        $field = $session->currentField;

        if (! $field) {
            // session بدون فیلد جاری معتبر - وضعیت غیرمنتظره؛ لغو و بازگشت به منو
            $this->sessionEngine->expire($session);
            $this->showMainMenu($msg->chatId, $bot, $adapter);

            return;
        }

        $result = $this->validationEngine->validate($field, $msg->text);

        if (! $result->valid) {
            $adapter->sendMessage($msg->chatId, $result->errorMessage);
            $this->sendField($msg->chatId, $field, $adapter);

            return;
        }

        $session->submission->values()->updateOrCreate(
            ['field_id' => $field->id],
            ['value' => $msg->text],
        );

        $nextField = $field->step->fields()
            ->where('display_order', '>', $field->display_order)
            ->orderBy('display_order')
            ->first();

        $nextStep = null;

        if (! $nextField) {
            $nextStep = $this->resolveNextStep($session->process, $field->step);
            $nextField = $nextStep?->fields()->orderBy('display_order')->first();
        }

        if (! $nextField) {
            $this->sessionEngine->complete($session);
            $adapter->sendMessage($msg->chatId, 'فرآیند با موفقیت تکمیل شد. متشکریم!');
            $this->notificationEngine->notifyAdmin($session->submission);

            return;
        }

        $this->sessionEngine->advance(
            $session,
            $field->id,
            $nextField->id,
            $nextStep?->id,
        );

        $this->sendField($msg->chatId, $nextField, $adapter);
    }

    protected function resolveNextStep(Process $process, ProcessStep $currentStep): ?ProcessStep
    {
        return $process->steps()
            ->where('display_order', '>', $currentStep->display_order)
            ->orderBy('display_order')
            ->first();
    }

    public function sendField(string $chatId, ProcessField $field, BotAdapter $adapter): void
    {
        if ($field->field_type === 'select') {
            $buttons = collect($field->options ?? [])
                ->map(fn (array $option) => ['value' => $option['value'], 'label' => $option['label']])
                ->all();

            $adapter->sendKeyboard($chatId, $field->label, $buttons);

            return;
        }

        if ($field->field_type === 'boolean') {
            $adapter->sendKeyboard($chatId, $field->label, [
                ['value' => 'true', 'label' => 'بله'],
                ['value' => 'false', 'label' => 'خیر'],
            ]);

            return;
        }

        $adapter->sendMessage($chatId, $field->label);
    }

    public function showMainMenu(string $chatId, Bot $bot, BotAdapter $adapter): void
    {
        $processes = $bot->processes()
            ->where('is_current_version', true)
            ->where('is_active', true)
            ->get();

        if ($processes->isEmpty()) {
            $adapter->sendMessage($chatId, 'در حال حاضر فرآیند فعالی برای شروع وجود ندارد.');

            return;
        }

        $buttons = $processes
            ->map(fn (Process $process) => ['value' => "process:{$process->id}", 'label' => $process->name])
            ->all();

        $adapter->sendKeyboard($chatId, 'لطفاً یکی از فرآیندهای زیر را انتخاب کنید:', $buttons);
    }
}
