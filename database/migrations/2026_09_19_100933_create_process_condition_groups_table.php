<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

        DB::statement(
            "ALTER TABLE process_condition_groups ADD CONSTRAINT chk_pcg_stop_no_target "
            . "CHECK (action <> 'stop' OR target_step_id IS NULL)"
        );

        DB::statement(
            "ALTER TABLE process_condition_groups ADD CONSTRAINT chk_pcg_nonstop_has_target "
            . "CHECK (action = 'stop' OR target_step_id IS NOT NULL)"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('process_condition_groups');
    }
};
