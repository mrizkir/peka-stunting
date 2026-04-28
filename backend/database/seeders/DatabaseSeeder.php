<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        
        $admin = User::firstOrCreate([
          'email' => 'admin@anugerahbintan.ac.id',
        ], [
          'name' => 'Admin',
          'password' => Hash::make('12345678'),
          'phone' => '081234567891',
          'gender' => 'L',
      ]);
      $admin->assignRole('admin');
    }
}
