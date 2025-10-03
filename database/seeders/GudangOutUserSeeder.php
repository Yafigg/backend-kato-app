<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GudangOutUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update Gudang Out user
        \App\Models\User::updateOrCreate(
            ['email' => 'gudang_out@kato.com'],
            [
            'name' => 'Gudang Out Manager',
            'email' => 'gudang_out@kato.com',
            'password' => bcrypt('password'),
            'phone' => '081234567890',
            'address' => 'Jl. Gudang Out No. 123',
            'user_type' => 'management',
            'management_subrole' => 'gudang_out',
            'is_verified' => true,
            'email_verified_at' => now(),
            ]
        );

        // Create some sample inventory items with ready_for_shipment status
        \App\Models\Inventory::create([
            'user_id' => 1,
            'product_name' => 'Padi Premium',
            'category' => 'Beras',
            'quantity' => 100.0,
            'unit' => 'Kg',
            'price_per_unit' => 15000.0,
            'harvest_date' => now()->subDays(5),
            'estimated_ready_date' => now()->addDays(1),
            'packaging_type' => 'Karung',
            'delivery_method' => 'Truk',
            'season' => 'Kemarau',
            'notes' => 'Siap untuk pengiriman',
            'status' => 'available',
        ]);

        \App\Models\Inventory::create([
            'user_id' => 1,
            'product_name' => 'Jagung Manis',
            'category' => 'Sayuran',
            'quantity' => 50.0,
            'unit' => 'Kg',
            'price_per_unit' => 8000.0,
            'harvest_date' => now()->subDays(3),
            'estimated_ready_date' => now()->addDays(2),
            'packaging_type' => 'Kardus',
            'delivery_method' => 'Pickup',
            'season' => 'Kemarau',
            'notes' => 'Kualitas premium',
            'status' => 'available',
        ]);

        \App\Models\Inventory::create([
            'user_id' => 1,
            'product_name' => 'Tomat Segar',
            'category' => 'Sayuran',
            'quantity' => 75.0,
            'unit' => 'Kg',
            'price_per_unit' => 12000.0,
            'harvest_date' => now()->subDays(1),
            'estimated_ready_date' => now(),
            'packaging_type' => 'Plastik',
            'delivery_method' => 'Sepeda Motor',
            'season' => 'Kemarau',
            'notes' => 'Segar dari kebun',
            'status' => 'available',
        ]);

        // Create some sample outbound shipments
        \App\Models\OutboundShipment::create([
            'shipment_code' => 'OUT-2025-001',
            'customer_name' => 'Toko Sumber Makmur',
            'customer_address' => 'Jl. Pasar No. 123, Jakarta',
            'customer_phone' => '081234567890',
            'delivery_method' => 'Truk',
            'estimated_delivery_date' => now()->addDays(2),
            'status' => 'delivered',
            'total_items' => 3,
            'total_weight' => 225.0,
            'shipped_at' => now()->subDays(1),
            'delivered_at' => now()->subHours(2),
            'user_id' => 1,
        ]);

        \App\Models\OutboundShipment::create([
            'shipment_code' => 'OUT-2025-002',
            'customer_name' => 'Pasar Induk Jakarta',
            'customer_address' => 'Jl. Induk Pasar No. 456, Jakarta',
            'customer_phone' => '081234567891',
            'delivery_method' => 'Pickup',
            'estimated_delivery_date' => now()->addDays(1),
            'status' => 'in_transit',
            'total_items' => 2,
            'total_weight' => 150.0,
            'shipped_at' => now()->subHours(6),
            'user_id' => 1,
        ]);

        \App\Models\OutboundShipment::create([
            'shipment_code' => 'OUT-2025-003',
            'customer_name' => 'Restoran Bintang Lima',
            'customer_address' => 'Jl. Restoran No. 789, Jakarta',
            'customer_phone' => '081234567892',
            'delivery_method' => 'Sepeda Motor',
            'estimated_delivery_date' => now()->addDays(1),
            'status' => 'ready_for_pickup',
            'total_items' => 1,
            'total_weight' => 50.0,
            'user_id' => 1,
        ]);
    }
}
