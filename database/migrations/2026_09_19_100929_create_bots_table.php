<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('platform', ['telegram', 'bale', 'rubika']);
            $table->string('name', 100);
            $table->string('token', 255);
            $table->string('webhook_url', 500)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();

            $table->unique(['platform', 'token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
};
