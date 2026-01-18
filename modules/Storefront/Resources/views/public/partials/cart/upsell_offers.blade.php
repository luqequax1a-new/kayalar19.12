@if (!empty($upsellData) && !empty($upsellData['rule']) && !empty($upsellData['offers']))
    @php
        $rule = $upsellData['rule'];
        $offers = $upsellData['offers'];
        $locale = locale();
    @endphp

    @php
        $offersData = collect($offers)->values()->map(function($offer) use ($locale) {
            $product = $offer["product"];
            $variant = $offer["variant"] ?? null;
            
            $image = $product->base_image->exists ? $product->base_image->url : null;
            if ($variant && $variant->base_image->exists) {
                $image = $variant->base_image->url;
            }
            
            $productName = $product->name;
            if ($variant && $variant->name) {
                $productName .= " - " . $variant->name;
            }
            
            $subtitle = null;
            if (is_array($offer["subtitle"]) && !empty($offer["subtitle"])) {
                $subtitle = $offer["subtitle"][$locale] ?? reset($offer["subtitle"]);
            } elseif (is_string($offer["subtitle"])) {
                $subtitle = $offer["subtitle"];
            }
            
            $originalPrice = (float) $offer["original_price"];
            $upsellPrice = (float) $offer["upsell_price"];
            $hasDiscount = $upsellPrice < $originalPrice && $originalPrice > 0;
            $discountPercent = null;
            
            if ($hasDiscount) {
                $base = max($originalPrice, 0.01);
                $discountPercent = (int) round(100 - ($upsellPrice * 100 / $base));
            }
            
            $hasVariants = $product->variants()->exists();
            $allVariants = $product->variants()->withoutGlobalScope('active')->get();
            $letCustomerChoose = $offer["let_customer_choose"] ?? (!$variant && $hasVariants);
            
            return [
                "offer_id" => $offer["offer_id"],
                "order" => $offer["order"] ?? 0,
                "trigger" => $offer["trigger"] ?? 'always',
                "product_id" => (int) $product->id,
                "variant_id" => $variant ? (int) $variant->id : null,
                "product_name" => $productName,
                "base_product_name" => $product->name,
                "subtitle" => $subtitle,
                "image" => $image,
                "base_image" => $product->base_image->exists ? $product->base_image->url : null,
                "listing_avif_srcset" => $product->base_image->exists ? ($product->base_image->listing_avif_srcset ?? null) : null,
                "listing_webp_srcset" => $product->base_image->exists ? ($product->base_image->listing_webp_srcset ?? null) : null,
                "listing_jpeg_srcset" => $product->base_image->exists ? ($product->base_image->listing_jpeg_srcset ?? null) : null,
                "thumb_webp_url" => $product->base_image->exists ? ($product->base_image->thumb_webp_url ?? null) : null,
                "thumb_jpeg_url" => $product->base_image->exists ? ($product->base_image->thumb_jpeg_url ?? null) : null,
                "card_webp_url" => $product->base_image->exists ? ($product->base_image->card_webp_url ?? null) : null,
                "card_jpeg_url" => $product->base_image->exists ? ($product->base_image->card_jpeg_url ?? null) : null,
                "original_price" => $originalPrice,
                "upsell_price" => $upsellPrice,
                "has_discount" => $hasDiscount,
                "discount_percent" => $discountPercent,
                "manage_stock" => $variant ? (bool) $variant->manage_stock : (bool) $product->manage_stock,
                "stock_qty" => $variant ? (float) $variant->qty : (float) $product->qty,
                "let_customer_choose" => (bool) $letCustomerChoose,
                "unit_decimal" => (bool) $product->unit_decimal,
                "unit_suffix" => (string) ($product->unit_suffix ?? ''),
                "unit_label" => (string) ($product->unit_label ?? ''),
                "unit_min" => (float) ($product->unit_min ?? 1),
                "unit_step" => (float) ($product->unit_step ?? 1),
                "unit_info_top" => (string) ($product->unit_info_top ?? ''),
                "unit_info_bottom" => (string) ($product->unit_info_bottom ?? ''),
                "variation_label" => (string) ($product->variations->pluck('name')->unique()->implode(' / ') ?: 'Seçenekler'),
                "variants" => $allVariants->map(fn($v) => [
                    'id' => (int) $v->id,
                    'name' => (string) $v->name,
                    'price' => (float) ($v->price ? $v->price->amount() : 0),
                    'selling_price' => (float) ($v->getSellingPrice() ? $v->getSellingPrice()->amount() : 0),
                    'qty' => (float) ($v->qty ?? 0),
                    'manage_stock' => (bool) $v->manage_stock,
                    'is_default' => (bool) $v->is_default,
                    'image' => $v->base_image->exists ? ($v->base_image->thumb_webp_url ?? $v->base_image->card_webp_url ?? $v->base_image->thumb_jpeg_url ?? $v->base_image->url) : null,
                ])->toArray(),
            ];
        });
    @endphp

    <div
        x-data='CartUpsellBox({
            rule: @json($rule),
            offers: @json($offersData),
            addUpsellUrl: @json(route("cart.upsell.store")),
            placement: @json($rule->show_on),
            countdownSeconds: @json($rule->has_countdown && (float) $rule->countdown_minutes > 0 ? max(((int) round((float) $rule->countdown_minutes)) * 60, 0) : null)
        })'
        id="fc-upsell-root"
        x-show="offers.length > activeIndex || showQuantityModal"
        x-cloak
        class="fc-upsell-wrapper"
    >
        <!-- Dynamic Offer Cards -->
        <template x-for="(offer, index) in offers" :key="index">
            <div 
                class="fc-upsell-offer-card upsell-enter" 
                x-show="activeIndex === index" 
                x-cloak
            >
                <!-- Card Header -->
                <div class="fc-upsell-header">
                    <h4 class="fc-upsell-header-title">
                        @if(trans('storefront::upsell.special_offers') !== 'storefront::upsell.special_offers')
                            {{ trans('storefront::upsell.special_offers') }}
                        @else
                            ÖZEL TEKLİF!
                        @endif
                    </h4>
                    <button type="button" @click="closeAll" class="fc-upsell-close-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <!-- Card Body -->
                <div class="fc-upsell-body">
                    <!-- Loading Overlay -->
                    <div class="fc-upsell-loading-overlay" x-show="adding" x-cloak>
                        <div class="fc-upsell-spinner"></div>
                        <div class="fc-upsell-loading-text">Sepete Ekleniyor...</div>
                    </div>

                    <!-- Timer -->
                    <div class="fc-upsell-timer" x-show="showCountdown">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                        </svg>
                        <span x-text="'Hemen Değerlendir: ' + countdownLabel"></span>
                    </div>

                    <!-- Product Item -->
                    <div class="fc-upsell-item">
                        <div class="fc-upsell-item-img">
                            <picture>
                                <template x-if="offer.listing_avif_srcset">
                                    <source type="image/avif" :srcset="offer.listing_avif_srcset" sizes="100px">
                                </template>
                                <template x-if="offer.listing_webp_srcset">
                                    <source type="image/webp" :srcset="offer.listing_webp_srcset" sizes="100px">
                                </template>
                                <img :src="offer.thumb_webp_url || offer.card_webp_url || offer.thumb_jpeg_url || offer.image || '/assets/public/images/placeholder.png'" :srcset="offer.listing_jpeg_srcset || null" sizes="100px" :alt="offer.product_name" loading="eager" fetchpriority="high" decoding="async" width="100" height="100">
                            </picture>
                        </div>
                        <div class="fc-upsell-item-content">
                            <div class="fc-upsell-item-subtitle" x-show="offer.subtitle" x-text="offer.subtitle"></div>
                            <div class="fc-upsell-item-name" x-text="offer.product_name"></div>
                            <div class="fc-upsell-item-price-row">
                                <template x-if="offer.has_discount">
                                    <span class="fc-upsell-price-old" x-text="formatCurrency(offer.original_price)"></span>
                                </template>
                                <span class="fc-upsell-price-new" x-text="formatCurrency(offer.upsell_price)"></span>
                                <template x-if="offer.has_discount">
                                    <span class="fc-upsell-badge" x-text="'-' + offer.discount_percent + '%'"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="fc-upsell-btn-row">
                        <button type="button" @click="nextOffer" class="fc-upsell-btn fc-btn-reject">
                            {{ trans('storefront::upsell.reject') ?? 'Hayır, Kalsın' }}
                        </button>
                        <button type="button" @click="handleAction" :disabled="adding" class="fc-upsell-btn fc-btn-add">
                            <span>Fırsatı Ekle</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Dynamic Quantity / Variant Modal (Bottom Sheet on Mobile) -->
        <template x-teleport="body">
            <div x-show="showQuantityModal" x-cloak class="fc-upsell-bottom-sheet-overlay" @click.self="closeQuantityModal">
                <div class="fc-upsell-bottom-sheet" @click.stop>
                    <!-- Drag Handle -->
                    <div class="fc-upsell-bottom-sheet-handle"></div>
                    
                    <!-- Header -->
                    <div class="fc-upsell-bottom-sheet-header">
                        <div class="fc-upsell-bottom-sheet-header-text">
                            <h3 class="fc-upsell-bottom-sheet-title" x-text="currentOffer?.product_name"></h3>
                            <p class="fc-upsell-bottom-sheet-subtitle" x-text="modalStep === 'variant' ? 'Varyant seçimi yapın' : 'Miktar belirleyin'"></p>
                        </div>
                        <button type="button" @click="closeQuantityModal" class="fc-upsell-bottom-sheet-close">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Step: Variant -->
                    <div x-show="modalStep === 'variant'" class="fc-upsell-bottom-sheet-content">
                        <div class="fc-upsell-variant-list">
                            <template x-for="v in currentOffer?.variants" :key="v.id">
                                <button type="button" class="fc-upsell-variant-item" :class="{'active': selectedVariantId === v.id}" @click="selectVariant(v)">
                                    <template x-if="v.image">
                                        <img :src="v.image" loading="eager" decoding="async" width="60" height="60">
                                    </template>
                                    <span x-text="v.name"></span>
                                    <svg x-show="selectedVariantId === v.id" class="fc-upsell-variant-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>
                            </template>
                        </div>
                        <div class="fc-upsell-bottom-sheet-actions">
                            <button type="button" @click="goToQuantityStep" :disabled="!selectedVariantId" class="fc-upsell-bottom-sheet-btn primary">
                                Devam Et
                            </button>
                        </div>
                    </div>

                    <!-- Step: Quantity -->
                    <div x-show="modalStep === 'quantity'" class="fc-upsell-bottom-sheet-content">
                        <!-- Unit Decimal Quantity Picker (Same as Product Detail) -->
                        <template x-if="currentOffer?.unit_decimal">
                            <div class="decimal-quantity-card">
                                <div class="decimal-quantity-header">
                                    <div>
                                        <div class="decimal-quantity-title">
                                            <span x-text="currentOffer?.unit_label || 'Uzunluk'"></span> (<span x-text="currentOffer?.unit_suffix"></span>)
                                        </div>
                                        <div class="decimal-quantity-desc" x-text="currentOffer?.unit_info_top"></div>
                                    </div>
                                </div>

                                <div class="decimal-quantity-main">
                                    <button
                                        type="button"
                                        class="btn-quantity minus"
                                        :disabled="selectedQty <= (currentOffer?.unit_min || 0.5)"
                                        @click="setQty(Number(selectedQty) - (currentOffer?.unit_step || 0.5))"
                                    >
                                        −
                                    </button>

                                    <div class="decimal-quantity-input">
                                        <span
                                            class="input-overlay"
                                            x-show="!isEditingQty"
                                            x-text="(currentOffer?.unit_decimal ? Number(selectedQty).toFixed(2).replace(/\.00$/, '') : selectedQty) + (currentOffer?.unit_suffix ? currentOffer?.unit_suffix : '')"
                                        ></span>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            :value="isEditingQty ? qtyInput : selectedQty"
                                            autocomplete="off"
                                            :min="currentOffer?.unit_min || 0.5"
                                            :max="currentOffer?.manage_stock ? currentOffer?.stock_qty : 999999"
                                            class="form-control input-quantity-decimal input-overlay-target"
                                            @focus="beginEditQty($event)"
                                            @blur="commitEditQty()"
                                            @input="onQtyInput($event)"
                                            @keydown.up="setQty(Number(selectedQty) + (currentOffer?.unit_step || 0.5))"
                                            @keydown.down="setQty(Number(selectedQty) - (currentOffer?.unit_step || 0.5))"
                                        >
                                        <span class="input-suffix" x-text="currentOffer?.unit_suffix"></span>
                                    </div>

                                    <button
                                        type="button"
                                        class="btn-quantity plus"
                                        :disabled="currentOffer?.manage_stock && (Number(selectedQty) + (currentOffer?.unit_step || 0.5)) > currentOffer?.stock_qty"
                                        @click="setQty(Number(selectedQty) + (currentOffer?.unit_step || 0.5))"
                                    >
                                        +
                                    </button>
                                </div>

                                <div class="decimal-quantity-chips">
                                    <button type="button" class="chip" :disabled="currentOffer?.manage_stock && currentOffer?.stock_qty < 0.5" @click="setQty(0.5)">0.5<span x-text="currentOffer?.unit_suffix"></span></button>
                                    <button type="button" class="chip" :disabled="currentOffer?.manage_stock && currentOffer?.stock_qty < 1" @click="setQty(1)">1<span x-text="currentOffer?.unit_suffix"></span></button>
                                    <button type="button" class="chip" :disabled="currentOffer?.manage_stock && currentOffer?.stock_qty < 2.5" @click="setQty(2.5)">2.5<span x-text="currentOffer?.unit_suffix"></span></button>
                                    <button type="button" class="chip chip--desktop-only" :disabled="currentOffer?.manage_stock && currentOffer?.stock_qty < 5" @click="setQty(5)">5<span x-text="currentOffer?.unit_suffix"></span></button>
                                    <button type="button" class="chip" :disabled="currentOffer?.manage_stock && currentOffer?.stock_qty < 10" @click="setQty(10)">10<span x-text="currentOffer?.unit_suffix"></span></button>
                                </div>

                                <div class="decimal-quantity-info" x-text="currentOffer?.unit_info_bottom"></div>
                            </div>
                        </template>

                        <!-- Regular Number Quantity Picker (Same as Product Detail) -->
                        <template x-if="!currentOffer?.unit_decimal">
                            <div class="number-picker-lg">
                                <label for="upsell-qty-multi">{{ trans('storefront::product.quantity') }}</label>

                                <div class="input-group-quantity">
                                    <button
                                        type="button"
                                        aria-label="decrease"
                                        class="btn btn-number btn-minus"
                                        :disabled="selectedQty <= (currentOffer?.unit_min || 1)"
                                        @click="setQty(Number(selectedQty) - (currentOffer?.unit_step || 1))"
                                    >−</button>

                                    <input
                                        type="text"
                                        :value="selectedQty"
                                        autocomplete="off"
                                        :min="currentOffer?.unit_min || 1"
                                        :max="currentOffer?.manage_stock ? currentOffer?.stock_qty : 999999"
                                        id="upsell-qty-multi"
                                        class="form-control input-number input-quantity"
                                        @focus="$event.target.select()"
                                        @input="setQty(Number($event.target.value))"
                                        @keydown.up="setQty(Number(selectedQty) + (currentOffer?.unit_step || 1))"
                                        @keydown.down="setQty(Number(selectedQty) - (currentOffer?.unit_step || 1))"
                                    >

                                    <button
                                        type="button"
                                        aria-label="increase"
                                        class="btn btn-number btn-plus"
                                        :disabled="currentOffer?.manage_stock && (Number(selectedQty) + (currentOffer?.unit_step || 1)) > currentOffer?.stock_qty"
                                        @click="setQty(Number(selectedQty) + (currentOffer?.unit_step || 1))"
                                    >+</button>
                                </div>
                            </div>
                        </template>

                        <!-- Price Summary -->
                        <div class="fc-upsell-bottom-sheet-summary">
                            <div class="fc-upsell-bottom-sheet-summary-row">
                                <span>Birim Fiyat</span>
                                <span x-text="formatCurrency(currentPrice)"></span>
                            </div>
                            <div class="fc-upsell-bottom-sheet-summary-row total">
                                <span>Toplam</span>
                                <span x-text="formatCurrency(currentPrice * selectedQty)"></span>
                            </div>
                        </div>

                        <div class="fc-upsell-bottom-sheet-actions">
                            <button type="button" @click="closeQuantityModal" class="fc-upsell-bottom-sheet-btn secondary">
                                İptal
                            </button>
                            <button type="button" @click="confirmQuantity" :disabled="adding" class="fc-upsell-bottom-sheet-btn primary">
                                <span x-show="!adding">Sepete Ekle</span>
                                <span x-show="adding">Ekleniyor...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        // CartUpsellBox component already registered in upsell-common.js
        // No need for duplicate inline JavaScript
    </script>
    @endpush
@endif
