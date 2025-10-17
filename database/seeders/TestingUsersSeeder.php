<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestingUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Admin
            [
                'name' => 'Admin Kato',
                'email' => 'admin@kato.com',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
                'management_subrole' => null,
                'email_verified_at' => now(),
            ],
            
            // Petani
            [
                'name' => 'Petani Kato',
                'email' => 'petani@kato.com',
                'password' => Hash::make('password'),
                'user_type' => 'petani',
                'management_subrole' => null,
                'email_verified_at' => now(),
            ],
            
            // Management - Gudang In
            [
                'name' => 'Gudang In Kato',
                'email' => 'gudang_in@kato.com',
                'password' => Hash::make('password'),
                'user_type' => 'management',
                'management_subrole' => 'gudang_in',
                'email_verified_at' => now(),
            ],
            
            // Management - Gudang Out
            [
                'name' => 'Gudang Out Kato',
                'email' => 'gudang_out@kato.com',
                'password' => Hash::make('password'),
                'user_type' => 'management',
                'management_subrole' => 'gudang_out',
                'email_verified_at' => now(),
            ],
            
            // Management - Produksi
            [
                'name' => 'Produksi Kato',
                'email' => 'produksi@kato.com',
                'password' => Hash::make('password'),
                'user_type' => 'management',
                'management_subrole' => 'produksi',
                'email_verified_at' => now(),
            ],
            
            // Management - Pemasaran (already exists, but update if needed)
            [
                'name' => 'Pemasaran Kato',
                'email' => 'pemasaran@kato.com',
                'password' => Hash::make('password'),
                'user_type' => 'management',
                'management_subrole' => 'pemasaran',
                'email_verified_at' => now(),
            ],
            
            // Customer (already exists, but update if needed)
            [
                'name' => 'Customer Kato',
                'email' => 'customer@kato.com',
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'management_subrole' => null,
                'email_verified_at' => now(),
            ],
            
            // Test User (already exists, but update if needed)
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'user_type' => 'petani',
                'management_subrole' => null,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
