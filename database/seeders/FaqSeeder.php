<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question'   => 'How long does shipping and delivery take?',
                'answer'     => 'Standard delivery inside Dhaka usually takes 24 to 48 hours. For deliveries outside Dhaka, please allow 2 to 4 business days. You will receive real-time notifications on your account dashboard as your order is confirmed, shipped, and out for delivery.',
                'category'   => 'Shipping & Delivery',
                'sort_order' => 1,
                'is_active'  => true,
            ],
            [
                'question'   => 'What payment methods do you support?',
                'answer'     => 'We accept Cash on Delivery (COD), Direct Bank Transfer / Manual Mobile Financial Services (bKash / Nagad), and secure international Card payments via Stripe.',
                'category'   => 'Orders & Payment',
                'sort_order' => 2,
                'is_active'  => true,
            ],
            [
                'question'   => 'How do I track my order status?',
                'answer'     => 'You can check your order progress at any time by logging into your account and navigating to Dashboard > My Orders. You also receive instant status update notifications whenever your order transitions to a new phase.',
                'category'   => 'Orders & Payment',
                'sort_order' => 3,
                'is_active'  => true,
            ],
            [
                'question'   => 'What should I do if my account has been blocked or suspended?',
                'answer'     => 'If your account has been blocked, you will be redirected to our Contact page. Please verify your email using our OTP contact form and explain your issue. Our administration team will review your account status and respond directly to your verified email address.',
                'category'   => 'Account & Security',
                'sort_order' => 4,
                'is_active'  => true,
            ],
            [
                'question'   => 'Are all skincare and cosmetic products authentic?',
                'answer'     => 'Yes, 100% authentic and ethically sourced directly from authorized manufacturers and verified distributors. Every batch is rigorously inspected for authenticity, safety, and expiration dates.',
                'category'   => 'Products & Authenticity',
                'sort_order' => 5,
                'is_active'  => true,
            ],
            [
                'question'   => 'What is your return and exchange policy?',
                'answer'     => 'We provide a hassle-free 7-day return policy for unopened items in their original packaging. If you received a damaged or incorrect item, contact us immediately with photo evidence for a prompt replacement or full refund.',
                'category'   => 'Returns & Refunds',
                'sort_order' => 6,
                'is_active'  => true,
            ],
        ];

        foreach ($faqs as $item) {
            Faq::updateOrCreate(
                ['question' => $item['question']],
                $item
            );
        }
    }
}
