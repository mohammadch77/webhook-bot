<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('process_key', 50);
            $table->smallInteger('version')->unsigned()->default(1);
            $table->boolean('is_current_version')->default(true);
            $table->string('description', 300)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by_admin_id')->constrained('admins');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['process_key', 'version']);
            $table->index(['process_key', 'is_current_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};
