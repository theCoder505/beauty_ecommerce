<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class FraudDetectionService
{
    /**
     * Common disposable/temporary email provider domains.
     */
    protected const DISPOSABLE_DOMAINS = [
        'mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com',
        'throwawaymail.com', 'yopmail.com', 'trashmail.com', 'sharklasers.com',
        'fakeinbox.com', 'dispostable.com',
    ];

    /**
     * Alias for analyze().
     */
    public function evaluate(Order $order): array
    {
        return $this->analyze($order);
    }

    /**
     * Analyze an order for potential fraud indicators.
     */
    public function analyze(Order $order): array
    {
        $score = 0;
        $flags = [];

        // 1. High Order Total Check
        $total = (float) $order->total;
        if ($total >= 10000) {
            $score += 30;
            $flags[] = "High order value (৳" . number_format($total, 2) . ") exceeds standard transaction threshold";
        } elseif ($total >= 5000) {
            $score += 15;
            $flags[] = "Above-average order value (৳" . number_format($total, 2) . ")";
        }

        // 2. Bulk Quantity Check
        $items = $order->relationLoaded('items') ? $order->items : $order->items()->get();
        $totalQty = $items->sum('quantity');
        $maxSingleQty = $items->max('quantity') ?? 0;

        if ($totalQty >= 10) {
            $score += 20;
            $flags[] = "Unusual bulk quantity: {$totalQty} items in a single order";
        } elseif ($maxSingleQty >= 5) {
            $score += 15;
            $flags[] = "High single-item quantity ({$maxSingleQty} units of one product)";
        }

        // 3. High Velocity / Rapid Orders Check (within last 24 hours)
        $oneDayAgo = Carbon::now()->subHours(24);
        $recentOrdersQuery = Order::where('id', '!=', $order->id)
            ->where('created_at', '>=', $oneDayAgo)
            ->where(function ($q) use ($order) {
                if ($order->user_id) {
                    $q->where('user_id', $order->user_id);
                }
                if ($order->shipping_email) {
                    $q->orWhere('shipping_email', $order->shipping_email);
                }
                if ($order->shipping_phone) {
                    $q->orWhere('shipping_phone', $order->shipping_phone);
                }
            });

        $recentCount = $recentOrdersQuery->count();
        if ($recentCount >= 2) {
            $score += 30;
            $flags[] = "Rapid order velocity: {$recentCount} other orders placed from this user/contact within 24 hours";
        } elseif ($recentCount === 1) {
            $score += 15;
            $flags[] = "Repeat order placed within 24 hours";
        }

        // 4. New Account Check (account created within 2 hours of order)
        if ($order->user) {
            $userCreatedAt = $order->user->created_at;
            $orderCreatedAt = $order->created_at ?? now();
            if ($userCreatedAt && $userCreatedAt->diffInMinutes($orderCreatedAt) <= 120) {
                $score += 15;
                $flags[] = "New customer account registered shortly before checkout (" . $userCreatedAt->diffForHumans($orderCreatedAt, true) . " prior)";
            }
        }

        // 5. Past Cancellation / Non-Payment History Check
        if ($order->user_id || $order->shipping_email) {
            $cancelledPast = Order::where('id', '!=', $order->id)
                ->whereIn('status', ['cancelled'])
                ->where(function ($q) use ($order) {
                    if ($order->user_id) {
                        $q->where('user_id', $order->user_id);
                    }
                    if ($order->shipping_email) {
                        $q->orWhere('shipping_email', $order->shipping_email);
                    }
                })->count();

            if ($cancelledPast >= 2) {
                $score += 25;
                $flags[] = "Customer profile has {$cancelledPast} previously cancelled orders";
            }
        }

        // 6. Name Mismatch Check (User Name vs Shipping Recipient Name)
        if ($order->user && !empty($order->user->name) && !empty($order->shipping_name)) {
            $userNameWords = array_filter(explode(' ', strtolower(trim($order->user->name))));
            $shippingWords = array_filter(explode(' ', strtolower(trim($order->shipping_name))));
            $overlap = array_intersect($userNameWords, $shippingWords);

            if (empty($overlap) && count($userNameWords) > 0 && count($shippingWords) > 0) {
                $score += 15;
                $flags[] = "Name mismatch: Account name ('{$order->user->name}') differs from recipient name ('{$order->shipping_name}')";
            }
        }

        // 7. Suspicious Phone Number Check
        $phone = preg_replace('/[^0-9]/', '', (string) $order->shipping_phone);
        if (strlen($phone) < 9 || preg_match('/(00000|11111|22222|33333|44444|55555|66666|77777|88888|99999|123456)/', $phone)) {
            $score += 25;
            $flags[] = "Suspicious or repetitive pattern in shipping phone number ('{$order->shipping_phone}')";
        }

        // 8. Incomplete or Suspicious Address Check
        $addr = strtolower(trim((string) $order->shipping_address));
        $suspiciousWords = ['test', 'asdf', 'fake', 'none', 'unknown', 'null', 'xxx'];
        $containsSuspicious = false;
        foreach ($suspiciousWords as $sw) {
            if (preg_match('/\b' . preg_quote($sw, '/') . '\b/', $addr)) {
                $containsSuspicious = true;
                break;
            }
        }

        if (strlen($addr) < 8 || $containsSuspicious) {
            $score += 25;
            $flags[] = "Shipping address appears incomplete or contains placeholder text ('{$order->shipping_address}')";
        }

        // 9. Disposable Email Domain Check
        $email = strtolower((string) ($order->shipping_email ?? $order->user?->email));
        $emailDomain = substr(strrchr($email, '@'), 1);
        if ($emailDomain && in_array($emailDomain, self::DISPOSABLE_DOMAINS)) {
            $score += 35;
            $flags[] = "Temporary / disposable email domain detected ('@{$emailDomain}')";
        }

        // Cap score at 100
        $finalScore = min(100, $score);

        // Determine Risk Level
        if ($finalScore >= 50) {
            $level = 'high';
            $recommendation = 'High fraud risk detected. Verify customer identity, phone number, and payment before processing.';
        } elseif ($finalScore >= 25) {
            $level = 'medium';
            $recommendation = 'Moderate risk indicators. Review customer delivery details or confirm via phone.';
        } else {
            $level = 'low';
            $recommendation = 'Standard low risk order. Ready for normal fulfillment.';
        }

        return [
            'score'          => $finalScore,
            'risk_score'     => $finalScore,
            'level'          => $level,
            'risk_level'     => $level,
            'flags'          => $flags,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Analyze and persist fraud metrics directly onto the order record.
     */
    public function evaluateAndSave(Order $order): array
    {
        $analysis = $this->analyze($order);

        $order->updateQuietly([
            'fraud_risk_score' => $analysis['score'],
            'fraud_risk_level' => $analysis['level'],
            'fraud_flags'      => $analysis['flags'],
        ]);

        return $analysis;
    }
}
