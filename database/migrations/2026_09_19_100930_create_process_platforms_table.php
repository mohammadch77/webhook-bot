<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_platforms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('process_id')->constrained('processes');
            $table->foreignUuid('bot_id')->constrained('bots');

            $table->unique(['process_id', 'bot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_platforms');
    }
};
