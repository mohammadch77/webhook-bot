<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('step_id')->constrained('process_steps');
            $table->string('field_key', 60);
            $table->string('label', 150);
            $table->enum('field_type', [
                'text', 'number', 'phone', 'textarea', 'select', 'boolean', 'file', 'image', 'date',
            ]);
            $table->boolean('is_required')->default(true);
            $table->json('options')->nullable();
            $table->smallInteger('display_order')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['step_id', 'field_key']);
        });

        DB::statement(
            "ALTER TABLE process_fields ADD CONSTRAINT chk_process_fields_boolean_not_required "
            . "CHECK (field_type <> 'boolean' OR is_required = FALSE)"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('process_fields');
    }
};
