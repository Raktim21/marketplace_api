<?php

namespace Database\Seeders;

use App\Models\PaymentGateways;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (!PaymentGateways::where('name', 'Stripe')->exists()) {            
            PaymentGateways::create([
                'name' => 'Stripe',
                'icon' => 'fab fa-stripe',
                'status' => 0,
            ]);
        }
        
        if (!PaymentGateways::where('name', 'PayPal')->exists()) {            
            PaymentGateways::create([
                'name' => 'PayPal',
                'icon' => 'fab fa-paypal',
                'status' => 0,
            ]);
        }

        if (!PaymentGateways::where('name', 'Sellhub')->exists()) {            
            PaymentGateways::create([
                'name' => 'Sellhub',
                'icon' => 'fas fa-money-bill-alt',
                'status' => 0,
            ]);
        }

        // if (!PaymentGateways::where('name', 'Sumup')->exists()) {            
        //     PaymentGateways::create([
        //         'name' => 'Sumup',
        //         'icon' => 'fas fa-money-bill-alt',
        //         'status' => 0,
        //     ]);
        // }
        
        if (!PaymentGateways::where('name', 'Crypto')->exists()) {            
            PaymentGateways::create([
                'name' => 'Crypto',
                'icon' => 'fab fa-bitcoin',
                'status' => 0,
            ]);
        }

        if (PaymentGateways::where('name', 'PayPal IPN')->exists()) { 
            // dd(PaymentGateways::where('name', 'PayPal IPN')->first());           
            PaymentGateways::where('name', 'PayPal IPN')->first()->update([
                'name' => 'PayPal F&F',
            ]);
        }else {
            PaymentGateways::create([
                'name' => 'PayPal F&F',
                'icon' => 'fab fa-paypal',
                'status' => 0,
            ]);
        }


        






        // PaymentGateways::where('name', 'Stripe')->first()->update([
        //     'icon' => 'fab fa-stripe',
        // ]);

        // PaymentGateways::where('name', 'PayPal')->first()->update([
        //     'icon' => 'fab fa-paypal',
        // ]);

        // PaymentGateways::where('name', 'Crypto')->first()->update([
        //     'icon' => 'fab fa-bitcoin',
        // ]);

        // PaymentGateways::where('name', 'Sellhub')->first()->update([
        //     'icon' => 'fas fa-money-bill-alt',
        // ]);

    }
}
