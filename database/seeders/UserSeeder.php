<?php

namespace Database\Seeders;

use App\Models\ProductVariantSerial;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::create([
        //     'name' => 'Admin',
        //     'email' => 'admin@admin.com',
        //     'password' => bcrypt('admin@123@'),
        // ]);

        $empty_serial = ProductVariantSerial::where('serial', '')->get();
        foreach ($empty_serial as $serial) {
            $serial->delete();
        }

    }
}
