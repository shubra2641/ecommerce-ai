<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'enabled' => true,
                'mode' => 'test',
                'credentials' => [
                    'test' => [
                        'client_id' => '',
                        'client_secret' => ''
                    ],
                    'live' => [
                        'client_id' => '',
                        'client_secret' => ''
                    ]
                ],
                'icon' => 'fab fa-paypal',
                'description' => 'Pay with PayPal - Secure and trusted payment method',
                'sort_order' => 1
            ],
            [
                'name' => 'Stripe',
                'slug' => 'stripe',
                'enabled' => true,
                'mode' => 'test',
                'credentials' => [
                    'test' => [
                        'publishable_key' => '',
                        'secret_key' => ''
                    ],
                    'live' => [
                        'publishable_key' => '',
                        'secret_key' => ''
                    ]
                ],
                'icon' => 'fab fa-stripe',
                'description' => 'Pay with Stripe - Accept payments from anywhere',
                'sort_order' => 2
            ],
            [
                'name' => 'Cash on Delivery',
                'slug' => 'cod',
                'enabled' => true,
                'mode' => 'live',
                'credentials' => null,
                'transfer_details' => 'Pay cash when your order is delivered to your doorstep.',
                'require_proof' => false,
                'icon' => 'fas fa-money-bill-wave',
                'description' => 'Pay cash when your order is delivered',
                'sort_order' => 3
            ],
            [
                'name' => 'Bank Transfer',
                'slug' => 'offline',
                'enabled' => true,
                'mode' => 'live',
                'credentials' => null,
                'transfer_details' => 'Please transfer the amount to our bank account and upload the receipt.',
                'require_proof' => true,
                'icon' => 'fas fa-university',
                'description' => 'Transfer money to our bank account',
                'sort_order' => 4
            ]
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(
                ['slug' => $gateway['slug']],
                $gateway
            );
        }
    }
}