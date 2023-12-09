<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Board;
use App\Models\BoardRight;
use App\Models\Lists;
use App\Models\Right;
use App\Models\User;
use App\Models\UserPassword;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $boards = Board::factory()->has(Lists::factory()->count(3))->count(1);
        // $boards->has(BoardRight::factory()->count(1));

        User::factory()
            ->has($boards)
            ->count(10)
            ->create();
        return;
    }
}
