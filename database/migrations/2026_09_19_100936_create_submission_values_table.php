<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('submission_id')->constrained('submissions');
            $table->foreignUuid('field_id')->constrained('process_fields');
            $table->text('value')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['submission_id', 'field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_values');
    }
};
