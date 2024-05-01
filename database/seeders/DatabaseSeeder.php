<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      User::factory()
      ->has(Account::factory()->count(15))
      ->has(Category::factory()->count(3))
      ->count(15)
      ->create(); 
    }
}
