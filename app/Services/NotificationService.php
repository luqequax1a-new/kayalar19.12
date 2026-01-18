<?php

namespace FleetCart\Services;

use FleetCart\Models\AdminNotification;

class NotificationService
{
    /**
     * Create a new notification
     */
    public static function create($type, $title, $message, $data = [], $options = [])
    {
        try {
            return AdminNotification::create([
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'icon' => $options['icon'] ?? null,
                'color' => $options['color'] ?? self::getDefaultColor($type),
                'link' => $options['link'] ?? null,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * Notify new order
     */
    public static function newOrder($order)
    {
        // Get display order number (with prefix)
        $orderNumber = $order->displayOrderNumber();
        
        // Get customer name
        $customerName = $order->customer_full_name;
        if (empty($customerName) || trim($customerName) === '') {
            $customerName = 'Misafir';
        }
        
        // Get payment method name
        $paymentMethod = $order->payment_method;
        $paymentMethodNames = [
            'cod' => 'Kapıda Ödeme',
            'bank_transfer' => 'Havale/EFT',
            'stripe' => 'Kredi Kartı (Stripe)',
            'paypal' => 'PayPal',
            'payu' => 'PayU',
            'iyzico' => 'İyzico',
        ];
        $paymentMethodName = $paymentMethodNames[$paymentMethod] ?? ucfirst(str_replace('_', ' ', $paymentMethod));
        
        return self::create(
            'new_order',
            'Yeni Sipariş!',
            "#{$orderNumber} numaralı {$order->total->format()} tutarında sipariş alındı.",
            [
                'order_id' => $order->id,
                'order_number' => $orderNumber,
                'customer_name' => $customerName,
                'total' => $order->total->amount(),
                'payment_method' => $paymentMethodName,
            ],
            [
                'color' => 'green',
                'link' => route('admin.orders.show', $order->id),
            ]
        );
    }

    /**
     * Notify abandoned cart
     */
    public static function abandonedCart($cart)
    {
        // Get items count from cart data
        $itemsCount = 0;
        if (!empty($cart->data)) {
            $items = $cart->data;
            if ($items instanceof \Illuminate\Support\Collection) {
                $itemsCount = $items->count();
            } elseif (is_array($items) || is_iterable($items)) {
                $itemsCount = count($items);
            }
        }
        
        return self::create(
            'abandoned_cart',
            'Sepet Terk Edildi',
            "{$cart->customer_email} sepetini terk etti. {$itemsCount} ürün",
            [
                'cart_id' => $cart->id,
                'customer_email' => $cart->customer_email,
                'items_count' => $itemsCount,
            ],
            [
                'color' => 'orange',
                'link' => route('admin.abandoned_carts.show', $cart->id),
            ]
        );
    }

    /**
     * Notify cart recovered
     */
    public static function cartRecovered($cart, $order)
    {
        return self::create(
            'cart_recovered',
            'Sepet Kurtarıldı! 🎉',
            "{$cart->customer_email} terk ettiği sepeti tamamladı. Sipariş: #{$order->id}",
            [
                'cart_id' => $cart->id,
                'order_id' => $order->id,
                'customer_email' => $cart->customer_email,
            ],
            [
                'color' => 'green',
                'link' => route('admin.orders.show', $order->id),
            ]
        );
    }

    /**
     * Notify new customer
     */
    public static function newCustomer($user)
    {
        $customerName = $user->full_name;
        if (empty($customerName) || trim($customerName) === '') {
            $customerName = $user->email;
        }
        
        return self::create(
            'new_customer',
            'Yeni Müşteri!',
            "Yeni müşteri kaydı yapıldı.",
            [
                'user_id' => $user->id,
                'customer_name' => $customerName,
                'email' => $user->email,
            ],
            [
                'color' => 'blue',
                'link' => route('admin.users.edit', $user->id),
            ]
        );
    }

    /**
     * Notify low stock
     */
    public static function lowStock($product)
    {
        return self::create(
            'low_stock',
            'Düşük Stok Uyarısı!',
            "{$product->name} stokta azaldı. Kalan: {$product->qty}",
            [
                'product_id' => $product->id,
                'qty' => $product->qty,
            ],
            [
                'color' => 'red',
                'link' => route('admin.products.edit', $product->id),
            ]
        );
    }

    /**
     * Notify product review
     */
    public static function productReview($review)
    {
        return self::create(
            'product_review',
            'Yeni Ürün Yorumu',
            "{$review->reviewer_name} bir ürüne yorum yaptı.",
            [
                'review_id' => $review->id,
                'product_id' => $review->product_id,
                'rating' => $review->rating,
                'reviewer_name' => $review->reviewer_name,
                'product_name' => $review->product?->name,
            ],
            [
                'color' => 'purple',
                'link' => route('admin.reviews.index'),
            ]
        );
    }

    /**
     * Notify new ticket
     */
    public static function newTicket($ticket)
    {
        $customerName = $ticket->user?->full_name ?? $ticket->guest_email ?? 'Misafir';
        
        return self::create(
            'new_ticket',
            'Yeni Destek Talebi!',
            "#{$ticket->id} - {$ticket->subject}",
            [
                'ticket_id' => $ticket->id,
                'customer_name' => $customerName,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
            ],
            [
                'color' => 'blue',
                'link' => route('admin.tickets.show', $ticket->id),
            ]
        );
    }

    /**
     * Notify new product question
     */
    public static function newQuestion($question)
    {
        $customerName = $question->customer_name;
        if (empty($customerName) || trim($customerName) === '') {
            $customerName = $question->user?->full_name ?? 'Misafir';
        }
        
        return self::create(
            'new_question',
            'Yeni Ürün Sorusu!',
            "{$customerName} bir ürün için soru sordu.",
            [
                'question_id' => $question->id,
                'product_id' => $question->product_id,
                'customer_name' => $customerName,
                'product_name' => $question->product?->name,
            ],
            [
                'color' => 'blue',
                'link' => route('admin.questions.edit', $question->id),
            ]
        );
    }

    /**
     * Get default color for notification type
     */
    private static function getDefaultColor($type)
    {
        $colors = [
            'new_order' => 'green',
            'abandoned_cart' => 'orange',
            'cart_recovered' => 'green',
            'new_customer' => 'blue',
            'low_stock' => 'red',
            'product_review' => 'purple',
            'contact_message' => 'blue',
            'new_ticket' => 'blue',
            'new_question' => 'blue',
        ];

        return $colors[$type] ?? 'blue';
    }

    /**
     * Get unread count
     */
    public static function getUnreadCount()
    {
        return AdminNotification::unread()->count();
    }

    /**
     * Get recent notifications
     */
    public static function getRecent($limit = 10)
    {
        return AdminNotification::recent($limit)->get();
    }

    /**
     * Mark all as read
     */
    public static function markAllAsRead()
    {
        return AdminNotification::unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Delete old notifications
     */
    public static function deleteOld($days = 30)
    {
        return AdminNotification::where('created_at', '<', now()->subDays($days))->delete();
    }
}
