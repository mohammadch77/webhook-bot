<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('process_condition_rules');
        Schema::dropIfExists('process_condition_groups');
    }

    public function down(): void
    {
        Schema::create('process_condition_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('process_id')->constrained('processes');
            $table->enum('action', ['show_step', 'skip_step', 'jump_to_step', 'stop']);
            $table->foreignUuid('target_step_id')->nullable()->constrained('process_steps');
            $table->string('stop_message', 300)->nullable();
            $table->smallInteger('display_order')->unsigned()->default(0);
            $table->timestamps();
        });

        Schema::create('process_condition_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained('process_condition_groups');
            $table->foreignUuid('field_id')->constrained('process_fields');
            $table->enum('operator', ['=', '!=', '>', '<', '>=', '<=']);
            $table->string('value', 200);
            $table->timestamps();
        });
    }
};
