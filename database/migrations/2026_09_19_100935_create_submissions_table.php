<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('process_id')->constrained('processes');
            $table->foreignUuid('bot_id')->constrained('bots');
            $table->enum('platform', ['telegram', 'bale', 'rubika']);
            $table->string('external_user_id', 100);
            $table->string('username', 100)->nullable();
            $table->enum('status', ['in_progress', 'completed', 'stopped', 'expired'])->default('in_progress');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();

            $table->index(['platform', 'external_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
