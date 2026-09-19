<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('process_id')->constrained('processes');
            $table->string('step_key', 50);
            $table->string('name', 100);
            $table->smallInteger('display_order')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['process_id', 'step_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_steps');
    }
};
