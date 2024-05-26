<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
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
      // admin
      User::create([
        'name' => 'Admin 1',
        'email' => 'admin1@admin.com',
        'password' => bcrypt('d0HFeFx8TN7YSnU4'),
        'is_admin' => true,
        'status' => 'active',
      ]);
      
      User::create([
        'name' => 'Admin 2',
        'email' => 'admin2@admin.com',
        'password' => bcrypt('Ujr2L39DT25JpLAK'),
        'email_verified_at' => now(),
        'is_admin' => true,
        'status' => 'active',
      ]);
      
      // user factory
      // User::factory()
      // ->has(Account::factory()->count(15))
      // ->has(Category::factory()->count(3))
      // ->count(15)
      // ->create(); 

      // Create 15 users
      User::factory(15)->create()->each(function ($user) {
        // Each user has 3 accounts
        $accounts = Account::factory(3)->make();
        $user->accounts()->saveMany($accounts);

        $accounts->each(function ($account) use ($user) {
            // Each account has 20 transactions
            $transactions = Transaction::factory(20)->make();

            // Each transaction belongs to a category
            $category = Category::factory()->create(['user_id' => $user->id]);

            $transactions->each(function ($transaction) use ($category) {
                $transaction->category_id = $category->id;
            });

            $account->transactions()->saveMany($transactions);
        });
      });
    }
}
