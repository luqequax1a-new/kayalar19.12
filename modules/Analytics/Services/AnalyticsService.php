<?php

namespace Modules\Analytics\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Product;

class AnalyticsService
{
    /**
     * Track e-commerce event to all enabled platforms
     */
    public function trackEvent(string $eventName, array $data = []): void
    {
        try {
            // Google Analytics 4
            if (setting('google_analytics_measurement_id') && setting('google_analytics_api_secret')) {
                $this->sendToGA4($eventName, $data);
            }

            // Facebook Conversion API
            if (setting('facebook_pixel_id') && setting('facebook_conversion_api_token')) {
                $this->sendToFacebookCAPI($eventName, $data);
            }

            // TikTok Events API
            if (setting('tiktok_pixel_id')) {
                $this->sendToTikTokEvents($eventName, $data);
            }
        } catch (\Exception $e) {
            Log::error('Analytics tracking error: ' . $e->getMessage(), [
                'event' => $eventName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send event to Google Analytics 4 Measurement Protocol
     */
    protected function sendToGA4(string $eventName, array $data): void
    {
        try {
            $measurementId = setting('google_analytics_measurement_id');
            $apiSecret = setting('google_analytics_api_secret');

            $clientId = $this->getClientId();
            $sessionId = $this->getSessionId();

            $payload = [
                'client_id' => $clientId,
                'events' => [
                    [
                        'name' => $eventName,
                        'params' => array_merge([
                            'session_id' => $sessionId,
                            'engagement_time_msec' => '100',
                        ], $data)
                    ]
                ]
            ];

            // Add user_id if authenticated
            if (auth()->check()) {
                $payload['user_id'] = (string) auth()->id();
            }

            $response = Http::post(
                "https://www.google-analytics.com/mp/collect?measurement_id={$measurementId}&api_secret={$apiSecret}",
                $payload
            );

            if (!$response->successful()) {
                Log::warning('GA4 tracking failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('GA4 tracking error: ' . $e->getMessage());
        }
    }

    /**
     * Send event to Facebook Conversion API
     */
    protected function sendToFacebookCAPI(string $eventName, array $data): void
    {
        try {
            $pixelId = setting('facebook_pixel_id');
            $accessToken = setting('facebook_conversion_api_token');

            // Map event names to Facebook standard events
            $fbEventName = $this->mapToFacebookEvent($eventName);

            $eventData = [
                'event_name' => $fbEventName,
                'event_time' => time(),
                'action_source' => 'website',
                'event_source_url' => url()->current(),
                'user_data' => $this->getUserData(),
            ];

            // Add custom data
            if (!empty($data)) {
                $eventData['custom_data'] = $data;
            }

            $payload = [
                'data' => [$eventData]
            ];

            $response = Http::post(
                "https://graph.facebook.com/v18.0/{$pixelId}/events?access_token={$accessToken}",
                $payload
            );

            if (!$response->successful()) {
                Log::warning('Facebook CAPI tracking failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Facebook CAPI tracking error: ' . $e->getMessage());
        }
    }

    /**
     * Send event to TikTok Events API
     */
    protected function sendToTikTokEvents(string $eventName, array $data): void
    {
        try {
            // TikTok Events API requires access token which is not commonly available
            // For now, we'll rely on client-side pixel tracking
            // This can be extended when TikTok access token is provided
        } catch (\Exception $e) {
            Log::error('TikTok Events API error: ' . $e->getMessage());
        }
    }

    /**
     * Track product view
     */
    public function trackProductView(Product $product): void
    {
        $data = [
            'currency' => currency(),
            'value' => $product->selling_price->convertToCurrentCurrency()->amount(),
            'items' => [
                [
                    'item_id' => (string) $product->id,
                    'item_name' => $product->name,
                    'price' => $product->selling_price->convertToCurrentCurrency()->amount(),
                    'quantity' => 1,
                ]
            ]
        ];

        $this->trackEvent('view_item', $data);
    }

    /**
     * Track add to cart
     */
    public function trackAddToCart(Product $product, int $quantity = 1, $variant = null): void
    {
        $price = $variant ? $variant->selling_price->convertToCurrentCurrency()->amount() : $product->selling_price->convertToCurrentCurrency()->amount();

        $data = [
            'currency' => currency(),
            'value' => $price * $quantity,
            'items' => [
                [
                    'item_id' => (string) $product->id,
                    'item_name' => $product->name,
                    'price' => $price,
                    'quantity' => $quantity,
                ]
            ]
        ];

        $this->trackEvent('add_to_cart', $data);
    }

    /**
     * Track begin checkout
     */
    public function trackBeginCheckout($cart): void
    {
        $items = [];
        foreach ($cart->items() as $item) {
            $items[] = [
                'item_id' => (string) $item->product->id,
                'item_name' => $item->product->name,
                'price' => $item->unitPrice()->amount(),
                'quantity' => $item->qty,
            ];
        }

        $data = [
            'currency' => currency(),
            'value' => $cart->total()->amount(),
            'items' => $items,
        ];

        $this->trackEvent('begin_checkout', $data);
    }

    /**
     * Track purchase
     */
    public function trackPurchase(Order $order): void
    {
        $items = [];
        foreach ($order->products as $product) {
            $items[] = [
                'item_id' => (string) $product->product_id,
                'item_name' => $product->name,
                'price' => $product->unit_price->amount(),
                'quantity' => $product->qty,
            ];
        }

        $data = [
            'transaction_id' => (string) $order->id,
            'currency' => $order->currency,
            'value' => $order->total->amount(),
            'tax' => $order->tax->amount(),
            'shipping' => $order->shipping_cost->amount(),
            'items' => $items,
        ];

        $this->trackEvent('purchase', $data);
    }

    /**
     * Get or generate client ID
     */
    protected function getClientId(): string
    {
        if (session()->has('ga_client_id')) {
            return session('ga_client_id');
        }

        $clientId = $this->generateUUID();
        session(['ga_client_id' => $clientId]);

        return $clientId;
    }

    /**
     * Get or generate session ID
     */
    protected function getSessionId(): string
    {
        if (session()->has('ga_session_id')) {
            return session('ga_session_id');
        }

        $sessionId = (string) time();
        session(['ga_session_id' => $sessionId]);

        return $sessionId;
    }

    /**
     * Generate UUID v4
     */
    protected function generateUUID(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Get user data for Facebook CAPI
     */
    protected function getUserData(): array
    {
        $userData = [
            'client_ip_address' => request()->ip(),
            'client_user_agent' => request()->userAgent(),
        ];

        if (auth()->check()) {
            $user = auth()->user();
            
            if ($user->email) {
                $userData['em'] = hash('sha256', strtolower(trim($user->email)));
            }

            if ($user->first_name) {
                $userData['fn'] = hash('sha256', strtolower(trim($user->first_name)));
            }

            if ($user->last_name) {
                $userData['ln'] = hash('sha256', strtolower(trim($user->last_name)));
            }

            if ($user->phone) {
                $userData['ph'] = hash('sha256', preg_replace('/[^0-9]/', '', $user->phone));
            }
        }

        return $userData;
    }

    /**
     * Map event names to Facebook standard events
     */
    protected function mapToFacebookEvent(string $eventName): string
    {
        $mapping = [
            'view_item' => 'ViewContent',
            'add_to_cart' => 'AddToCart',
            'begin_checkout' => 'InitiateCheckout',
            'purchase' => 'Purchase',
            'search' => 'Search',
        ];

        return $mapping[$eventName] ?? $eventName;
    }
}
