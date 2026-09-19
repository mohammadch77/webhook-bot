<?php

namespace App\Bot;

use App\Bot\Contracts\BotAdapter;
use App\Bot\DTOs\IncomingMessage;
use App\Bot\Engines\FormEngine;
use App\Bot\Engines\SessionEngine;
use App\Models\Bot;
use App\Models\Process;
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
        $processId = $this->extractProcessSelection($msg);

        if ($processId !== null) {
            $process = $bot->processes()
                ->where('is_current_version', true)
                ->where('is_active', true)
                ->where('processes.id', $processId)
                ->first();

            if (! $process) {
                $adapter->sendMessage($msg->chatId, 'این فرآیند در دسترس نیست.');

                return;
            }

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

    protected function extractProcessSelection(IncomingMessage $msg): ?string
    {
        if ($msg->type !== 'callback') {
            return null;
        }

        if (! Str::startsWith($msg->text, 'process:')) {
            return null;
        }

        return Str::after($msg->text, 'process:');
    }
}
