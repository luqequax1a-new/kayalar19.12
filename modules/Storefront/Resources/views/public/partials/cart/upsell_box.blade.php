@if (! empty($upsellOffer) && ! empty($upsellOffer['rule']))
    @php
        /** @var \Modules\Cart\Entities\CartUpsellRule $rule */
        $rule    = $upsellOffer['rule'];
        $product = $upsellOffer['product'];
        $variant = $upsellOffer['variant'] ?? null;

        $image = $product->base_image->path ?? $product->base_image->thumb ?? null;

        $originalPrice = (float) $upsellOffer['original_price'];
        $upsellPrice   = (float) $upsellOffer['upsell_price'];

        $hasDiscount = $upsellPrice < $originalPrice && $originalPrice > 0;

        $discountPercent = null;
        if ($hasDiscount) {
            $base = max($originalPrice, 0.01);
            $discountPercent = (int) round(100 - ($upsellPrice * 100 / $base));
        }

        $ruleSubtitle = null;
        if (is_array($rule->subtitle) && ! empty($rule->subtitle)) {
            $locale = $locale ?? locale();
            $ruleSubtitle = $rule->subtitle[$locale] ?? reset($rule->subtitle);
        } elseif (is_string($rule->subtitle)) {
            $ruleSubtitle = $rule->subtitle;
        }

        $payload = [
            'rule_id'                => $rule->id,
            'product_id'             => $product->id,
            'preselected_variant_id' => $variant ? $variant->id : null,
            'original_price'         => $originalPrice,
            'upsell_price'           => $upsellPrice,
            'subtitle'               => $ruleSubtitle,
            'image'                  => $image,
            'has_discount'           => $hasDiscount,
            'countdown_seconds'      => $rule->has_countdown && $rule->countdown_minutes
                ? max(((int) round((float) $rule->countdown_minutes)) * 60, 0)
                : null,
        ];
    @endphp

    <style>
        .fc-cart-upsell-box {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 18px 18px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
            box-sizing: border-box;
            overflow: hidden;
        }

        .fc-cart-upsell-main {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        @media (max-width: 639px) {
            .fc-cart-upsell-box {
                text-align: center;
            }

            .fc-cart-upsell-main {
                align-items: center;
            }

            .fc-cart-upsell-product-name,
            .fc-cart-upsell-title,
            .fc-cart-upsell-subtitle {
                text-align: center;
            }

            .fc-cart-upsell-prices {
                justify-content: center;
            }

            .fc-cart-upsell-actions {
                align-items: stretch;
            }
        }

        @media (min-width: 640px) {
            .fc-cart-upsell-main {
                flex-direction: row;
                align-items: flex-start;
            }
        }

        .fc-cart-upsell-image-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
            margin-top: -4px;
        }

        .fc-cart-upsell-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .fc-cart-upsell-title {
            color: #111827;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.15rem;
        }

        .fc-cart-upsell-subtitle {
            color: #6b7280;
            font-size: 0.8rem;
            margin-bottom: 0.15rem;
        }

        .fc-cart-upsell-product-name {
            color: #111827;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .fc-cart-upsell-prices {
            display: inline-flex;
            align-items: baseline;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .fc-cart-upsell-price-old {
            color: #9ca3af;
            font-size: 0.9rem;
            text-decoration: line-through;
        }

        .fc-cart-upsell-price-new {
            color: #111827;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .fc-cart-upsell-footer {
            border-top: 1px solid #f3f4f6;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
        }

        .fc-cart-upsell-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        @media (min-width: 640px) {
            .fc-cart-upsell-actions {
                flex-direction: row;
            }
            .fc-cart-upsell-actions button {
                flex: 1 1 0;
                min-width: 0;
            }
        }

        .upsell-btn-primary {
            background: #111827;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 600;
            border: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .upsell-btn-primary[disabled] {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .upsell-btn-secondary {
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .fc-cart-upsell-countdown {
            width: 100%;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.35rem 0.5rem;
            border-radius: 999px;
            margin-bottom: 0.75rem;
            background-color: #fef2f2;
            color: #b91c1c;
        }
    </style>

    <div
        x-data='CartUpsellBox({
            offer: @json($payload),
            addUpsellUrl: @json(route("cart.upsell.store"))
        })'
        x-show="show"
        x-cloak
        class="fc-cart-upsell-box w-100 mb-3"
    >
        <div
            class="fc-cart-upsell-countdown"
            x-show="showCountdown"
            x-text="`{{ trans('storefront::upsell.remaining_time', ['time' => '']) }}`.replace('  ', ' ') + countdownLabel"
        ></div>

        <div class="fc-cart-upsell-main">
            @if ($image)
                <div class="fc-cart-upsell-image-wrapper">
                    <img
                        src="{{ $image }}"
                        alt="{{ $product->name }}"
                        loading="lazy"
                    />
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="fc-cart-upsell-title">
                    @if ($discountPercent)
                        {{ $discountPercent }}% {{ __('Anlık İndirim!') }}
                    @else
                        {{ trans('storefront::upsell.default_title') }}
                    @endif
                </div>

                <div class="fc-cart-upsell-subtitle">
                    {{ $ruleSubtitle ?: trans('storefront::upsell.default_subtitle') }}
                </div>

                <div class="fc-cart-upsell-product-name">
                    {{ $product->name }}
                </div>

                <div class="fc-cart-upsell-prices">
                    @if ($hasDiscount)
                        <span class="fc-cart-upsell-price-old">
                            {{ number_format($originalPrice, 2) }}
                        </span>
                    @endif

                    <span class="fc-cart-upsell-price-new">
                        {{ number_format($upsellPrice, 2) }}
                    </span>
                </div>

                <div class="fc-cart-upsell-footer">
                    <div class="fc-cart-upsell-actions">
                        <button
                            type="button"
                            @click="reject"
                            class="upsell-btn-secondary"
                        >
                            {{ trans('storefront::upsell.reject') }}
                        </button>

                        <button
                            type="button"
                            @click="add"
                            :disabled="adding"
                            class="upsell-btn-primary"
                        >
                            <span x-show="!adding">
                                {{ trans('storefront::upsell.add_to_order') }}
                            </span>
                            <span x-show="adding">
                                {{ trans('storefront::upsell.adding') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
