<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bot_id')->constrained('bots');
            $table->string('external_user_id', 100);
            $table->foreignUuid('process_id')->constrained('processes');
            $table->foreignUuid('submission_id')->constrained('submissions');
            $table->foreignUuid('current_step_id')->constrained('process_steps');
            $table->foreignUuid('current_field_id')->nullable()->constrained('process_fields');
            $table->json('state')->nullable();
            $table->timestamp('last_activity_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['bot_id', 'external_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_sessions');
    }
};
