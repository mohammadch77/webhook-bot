<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_condition_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained('process_condition_groups');
            $table->foreignUuid('field_id')->constrained('process_fields');
            $table->enum('operator', ['=', '!=', '>', '<', '>=', '<=']);
            $table->string('value', 200);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_condition_rules');
    }
};
