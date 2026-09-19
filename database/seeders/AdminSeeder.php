<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@webhook-bot.test'],
            [
                'name' => 'Admin',
                'password' => 'password',
            ]
        );
    }
}
