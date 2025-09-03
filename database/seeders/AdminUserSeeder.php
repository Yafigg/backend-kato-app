<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin Kato App',
            'email' => 'admin@katoapp.com',
            'password' => Hash::make('admin123'),
            'phone' => '081234567890',
            'address' => 'SMK Telkom Malang',
            'user_type' => 'admin',
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        // Create Sample Petani User
        User::create([
            'name' => 'Irwandi Petani',
            'email' => 'irwandi@petani.com',
            'password' => Hash::make('petani123'),
            'phone' => '081234567891',
            'address' => 'Desa Tani, Malang',
            'bank_account' => '1234567890',
            'user_type' => 'petani',
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        // Create Sample Management Users
        User::create([
            'name' => 'Budi Gudang In',
            'email' => 'budi@gudangin.com',
            'password' => Hash::make('gudang123'),
            'phone' => '081234567892',
            'address' => 'Gudang Utama',
            'user_type' => 'management',
            'management_subrole' => 'gudang_in',
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        User::create([
            'name' => 'Siti Produksi',
            'email' => 'siti@produksi.com',
            'password' => Hash::make('produksi123'),
            'phone' => '081234567893',
            'address' => 'Pabrik Produksi',
            'user_type' => 'management',
            'management_subrole' => 'produksi',
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        User::create([
            'name' => 'Ahmad Pemasaran',
            'email' => 'ahmad@pemasaran.com',
            'password' => Hash::make('pemasaran123'),
            'phone' => '081234567894',
            'address' => 'Kantor Pemasaran',
            'user_type' => 'management',
            'management_subrole' => 'pemasaran',
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        User::create([
            'name' => 'Dedi Gudang Out',
            'email' => 'dedi@gudangout.com',
            'password' => Hash::make('gudangout123'),
            'phone' => '081234567897',
            'address' => 'Gudang Keluar',
            'user_type' => 'management',
            'management_subrole' => 'gudang_out',
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        // Create Sample Customer User
        User::create([
            'name' => 'Toko Segar Customer',
            'email' => 'customer@tokosegar.com',
            'password' => Hash::make('customer123'),
            'phone' => '081234567895',
            'address' => 'Toko Segar, Malang',
            'user_type' => 'customer',
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }
}
