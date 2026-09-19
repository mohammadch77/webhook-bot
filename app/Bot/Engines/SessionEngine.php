<?php

namespace App\Bot\Engines;

use App\Bot\DTOs\IncomingMessage;
use App\Models\Bot;
use App\Models\ConversationSession;
use App\Models\Process;
use App\Models\Submission;

class SessionEngine
{
    public function getOrCreate(IncomingMessage $msg, Bot $bot): ?ConversationSession
    {
        return ConversationSession::where('bot_id', $bot->id)
            ->where('external_user_id', $msg->chatId)
            ->first();
    }

    public function create(Bot $bot, IncomingMessage $msg, Process $process): ConversationSession
    {
        $submission = Submission::create([
            'process_id' => $process->id,
            'bot_id' => $bot->id,
            'platform' => $bot->platform,
            'external_user_id' => $msg->chatId,
            'username' => $msg->username,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $firstStep = $process->steps()->orderBy('display_order')->first();
        $firstField = $firstStep?->fields()->orderBy('display_order')->first();

        return ConversationSession::create([
            'bot_id' => $bot->id,
            'external_user_id' => $msg->chatId,
            'process_id' => $process->id,
            'submission_id' => $submission->id,
            'current_step_id' => $firstStep?->id,
            'current_field_id' => $firstField?->id,
            'state' => [],
        ]);
    }

    public function advance(ConversationSession $session, string $fieldId, ?string $nextFieldId, ?string $nextStepId): void
    {
        $session->update([
            'current_step_id' => $nextStepId ?? $session->current_step_id,
            'current_field_id' => $nextFieldId,
        ]);
    }

    public function complete(ConversationSession $session): void
    {
        $session->submission()->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $session->delete();
    }

    public function expire(ConversationSession $session): void
    {
        $session->submission()->update([
            'status' => 'expired',
        ]);

        $session->delete();
    }
}
