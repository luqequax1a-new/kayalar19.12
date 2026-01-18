<?php

namespace Modules\Cart\Console\Commands;

use Illuminate\Console\Command;
use Modules\Cart\Entities\Cart;
use Modules\Cart\Mail\AbandonedCartMail;
use Modules\Coupon\Entities\Coupon;
use Modules\Sms\Sms;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendAbandonedCartReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:send-abandoned-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends email and SMS reminders for abandoned carts.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!setting('abandoned_cart_reminder_enabled')) {
            $this->info('Abandoned cart reminders are disabled in settings.');
            return;
        }

        $delay1 = (int) setting('abandoned_cart_reminder_delay_hours', 1);
        $delay2 = (int) setting('abandoned_cart_reminder_2_delay_hours', 24);
        $delay3 = (int) setting('abandoned_cart_reminder_3_delay_hours', 48);

        // Get abandoned carts that need reminders
        $carts = Cart::where('id', 'like', '%_cart_items')
            ->where('is_recovered', false)
            ->where('updated_at', '>=', now()->subDays(7)) // Only last 7 days
            ->where('data', '!=', 'a:0:{}') // Exclude empty serialized arrays
            ->where('data', '!=', 'b:0;') // Exclude boolean false
            ->whereNotNull('data')
            ->where(function($q) {
                $q->whereNotNull('customer_email')
                  ->orWhere('is_clicked', true)
                  ->orWhere('is_recovered', true);
            })
            ->where(function ($query) use ($delay1, $delay2, $delay3) {
                // First reminder: after initial delay hours
                $query->where(function($q) use ($delay1) {
                    if (setting('abandoned_cart_reminder_1_enabled')) {
                        $q->whereNull('last_notified_at')
                          ->where('updated_at', '<=', now()->subHours($delay1));
                    } else {
                        $q->whereRaw('1=0');
                    }
                })
                // Second reminder: X hours after first
                ->orWhere(function($q) use ($delay2) {
                    if (setting('abandoned_cart_reminder_2_enabled')) {
                        $q->where('reminder_count', 1)
                          ->where('last_notified_at', '<=', now()->subHours($delay2));
                    } else {
                        $q->whereRaw('1=0');
                    }
                })
                // Third reminder: Y hours after second
                ->orWhere(function($q) use ($delay3) {
                    if (setting('abandoned_cart_reminder_3_enabled')) {
                        $q->where('reminder_count', 2)
                          ->where('last_notified_at', '<=', now()->subHours($delay3));
                    } else {
                        $q->whereRaw('1=0');
                    }
                });
            })
            ->where('reminder_count', '<', 3) // Max 3 reminders
            ->get();

        if ($carts->isEmpty()) {
            $this->info('No abandoned carts found to remind.');
            return;
        }

        $this->info("Found {$carts->count()} abandoned carts. Starting processing...");

        $sent = 0;
        foreach ($carts as $cart) {
            // Extra safety: Check if cart actually has items
            if (empty($cart->data) || count($cart->data) === 0) {
                // Skip empty carts, don't mark as recovered
                continue;
            }

            if ($this->processCart($cart)) {
                $sent++;
            }
        }

        $this->info("Sent {$sent} reminders successfully.");
    }

    protected function processCart(Cart $cart)
    {
        $success = false;
        $coupon = null;
        $reminderRound = $cart->reminder_count + 1;

        // 1. Handle Coupon Generation if enabled
        if (setting('abandoned_cart_coupon_enabled')) {
            $coupon = $this->getOrCreateCoupon($cart, $reminderRound);
        }

        // 2. Send Email
        if ($cart->customer_email) {
            try {
                Mail::to($cart->customer_email)->send(new AbandonedCartMail($cart, $coupon));
                $this->info("✓ Email #{$reminderRound} sent to: {$cart->customer_email}");
                $success = true;
            } catch (\Exception $e) {
                $this->error("✗ Failed to send email to {$cart->customer_email}: " . $e->getMessage());
            }
        }

        // 3. Send SMS
        if (setting('abandoned_cart_sms_enabled') && $cart->customer_phone) {
            $message = setting('abandoned_cart_sms_message');
            $message = str_replace(
                ['[customer_name]', '[cart_url]', '[store_name]', '[coupon_code]'],
                [
                    trim(($cart->customer_first_name . ' ' . $cart->customer_last_name)) ?: 'Değerli Müşterimiz',
                    route('cart.track', ['id' => $cart->id, 'redirect_to' => route('cart.index')]),
                    setting('store_name'),
                    $coupon ? $coupon->code : ''
                ],
                $message
            );

            try {
                Sms::send($cart->customer_phone, $message);
                $this->info("✓ SMS sent to: {$cart->customer_phone}");
                $success = true;
            } catch (\Exception $e) {
                $this->error("✗ Failed to send SMS to {$cart->customer_phone}: " . $e->getMessage());
            }
        }

        // 4. Update Cart State and Create Notification only if at least one notification was sent
        if ($success) {
            $cart->update([
                'last_notified_at' => now(),
                'reminder_count' => $reminderRound,
            ]);
            
            // Create admin notification for abandoned cart (only on first reminder)
            if ($reminderRound == 1) {
                \FleetCart\Services\NotificationService::abandonedCart($cart);
            }
        }

        return $success;
    }

    protected function getOrCreateCoupon(Cart $cart, $round)
    {
        // Invalidate any old coupons for this cart
        Coupon::where('cart_id', $cart->id)
            ->where('is_abandoned_cart_coupon', true)
            ->update(['is_active' => false]);

        // Get discount based on round
        $discount = (int) setting('abandoned_cart_coupon_discount_percent', 10);
        if ($round === 2) {
            $discount = (int) setting('abandoned_cart_coupon_2_discount_percent', $discount);
        } elseif ($round === 3) {
            $discount = (int) setting('abandoned_cart_coupon_3_discount_percent', $discount);
        }

        $validDays = (int) setting('abandoned_cart_coupon_valid_days', 3);
        $code = 'SEPET-' . strtoupper(Str::random(6));

        return Coupon::create([
            locale() => ['name' => 'Terk Edilmiş Sepet İndirimi'],
            'code' => $code,
            'is_percent' => true,
            'value' => $discount,
            'free_shipping' => false,
            'is_active' => true,
            'start_date' => now(),
            'end_date' => now()->addDays($validDays),
            'usage_limit_per_coupon' => 1,
            'usage_limit_per_customer' => 1,
            'is_abandoned_cart_coupon' => true,
            'cart_id' => $cart->id,
            'customer_id' => $cart->user_id,
        ]);
    }
}
