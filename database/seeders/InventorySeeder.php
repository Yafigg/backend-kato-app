<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\User;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a petani user
        $petani = User::firstOrCreate(
            ['email' => 'petani@kato.com'],
            [
                'name' => 'Petani Kato',
                'password' => bcrypt('password'),
                'phone' => '081234567892',
                'address' => 'Jawa Tengah, Indonesia',
                'user_type' => 'petani',
                'is_verified' => true,
                'verified_at' => now(),
            ]
        );

        // Create inventory items for bubuk umbi-umbian
        $inventoryItems = [
            [
                'user_id' => $petani->id,
                'product_name' => 'Bubuk Tepung Bayam Organik',
                'category' => 'Sayuran',
                'subcategory' => 'Bayam',
                'quantity' => 50.0,
                'unit' => 'kg',
                'price_per_unit' => 45000,
                'harvest_date' => now()->subDays(5),
                'estimated_ready_date' => now()->addDays(2),
                'packaging_type' => 'Plastik',
                'delivery_method' => 'Pickup',
                'season' => 'Kemarau',
                'photos' => ['assets/images/bayam_powder.jpg'],
                'status' => 'available',
                'notes' => 'Bubuk bayam organik kaya nutrisi, tinggi zat besi dan vitamin A',
            ],
            [
                'user_id' => $petani->id,
                'product_name' => 'Bubuk Jahe Premium',
                'category' => 'Rempah',
                'subcategory' => 'Jahe',
                'quantity' => 75.0,
                'unit' => 'kg',
                'price_per_unit' => 35000,
                'harvest_date' => now()->subDays(3),
                'estimated_ready_date' => now()->addDays(1),
                'packaging_type' => 'Plastik',
                'delivery_method' => 'Pickup',
                'season' => 'Kemarau',
                'photos' => ['assets/images/jahe_powder.jpg'],
                'status' => 'available',
                'notes' => 'Bubuk jahe asli dengan khasiat menghangatkan tubuh dan meningkatkan imunitas',
            ],
            [
                'user_id' => $petani->id,
                'product_name' => 'Bubuk Labu Kuning Organik',
                'category' => 'Umbi',
                'subcategory' => 'Labu',
                'quantity' => 25.0,
                'unit' => 'kg',
                'price_per_unit' => 65000,
                'harvest_date' => now()->subDays(7),
                'estimated_ready_date' => now()->addDays(3),
                'packaging_type' => 'Plastik',
                'delivery_method' => 'Pickup',
                'season' => 'Kemarau',
                'photos' => ['assets/images/labu_powder.jpg'],
                'status' => 'available',
                'notes' => 'Bubuk labu kuning organik kaya beta-karoten dan serat untuk kesehatan mata',
            ],
            [
                'user_id' => $petani->id,
                'product_name' => 'Bubuk Ubi Jalar Ungu',
                'category' => 'Umbi',
                'subcategory' => 'Ubi Jalar',
                'quantity' => 40.0,
                'unit' => 'kg',
                'price_per_unit' => 55000,
                'harvest_date' => now()->subDays(4),
                'estimated_ready_date' => now()->addDays(2),
                'packaging_type' => 'Plastik',
                'delivery_method' => 'Pickup',
                'season' => 'Kemarau',
                'photos' => ['assets/images/ubi_ungu_powder.jpg'],
                'status' => 'available',
                'notes' => 'Bubuk ubi jalar ungu premium dengan antosianin tinggi untuk anti-aging',
            ],
            [
                'user_id' => $petani->id,
                'product_name' => 'Bubuk Singkong Organik',
                'category' => 'Umbi',
                'subcategory' => 'Singkong',
                'quantity' => 0.0, // Out of stock
                'unit' => 'kg',
                'price_per_unit' => 30000,
                'harvest_date' => now()->subDays(10),
                'estimated_ready_date' => now()->addDays(5),
                'packaging_type' => 'Plastik',
                'delivery_method' => 'Pickup',
                'season' => 'Kemarau',
                'photos' => ['assets/images/singkong_powder.jpg'],
                'status' => 'sold',
                'notes' => 'Bubuk singkong organik bebas gluten, cocok untuk diet dan penderita diabetes',
            ],
            [
                'user_id' => $petani->id,
                'product_name' => 'Bubuk Wortel Premium',
                'category' => 'Sayuran',
                'subcategory' => 'Wortel',
                'quantity' => 30.0,
                'unit' => 'kg',
                'price_per_unit' => 40000,
                'harvest_date' => now()->subDays(2),
                'estimated_ready_date' => now()->addDays(1),
                'packaging_type' => 'Plastik',
                'delivery_method' => 'Pickup',
                'season' => 'Kemarau',
                'photos' => ['assets/images/wortel_powder.jpg'],
                'status' => 'available',
                'notes' => 'Bubuk wortel premium kaya vitamin A dan beta-karoten untuk kesehatan mata',
            ],
            [
                'user_id' => $petani->id,
                'product_name' => 'Bubuk Tepung Kangkung Organik',
                'category' => 'Sayuran',
                'subcategory' => 'Kangkung',
                'quantity' => 35.0,
                'unit' => 'kg',
                'price_per_unit' => 38000,
                'harvest_date' => now()->subDays(6),
                'estimated_ready_date' => now()->addDays(2),
                'packaging_type' => 'Plastik',
                'delivery_method' => 'Pickup',
                'season' => 'Kemarau',
                'photos' => ['assets/images/kangkung_powder.jpg'],
                'status' => 'available',
                'notes' => 'Bubuk kangkung organik kaya vitamin K dan folat untuk kesehatan tulang',
            ],
            [
                'user_id' => $petani->id,
                'product_name' => 'Bubuk Kunyit Premium',
                'category' => 'Rempah',
                'subcategory' => 'Kunyit',
                'quantity' => 20.0,
                'unit' => 'kg',
                'price_per_unit' => 60000,
                'harvest_date' => now()->subDays(8),
                'estimated_ready_date' => now()->addDays(4),
                'packaging_type' => 'Plastik',
                'delivery_method' => 'Pickup',
                'season' => 'Kemarau',
                'photos' => ['assets/images/kunyit_powder.jpg'],
                'status' => 'available',
                'notes' => 'Bubuk kunyit premium dengan kurkumin tinggi untuk anti-inflamasi',
            ],
        ];

        foreach ($inventoryItems as $item) {
            Inventory::create($item);
        }
    }
}
