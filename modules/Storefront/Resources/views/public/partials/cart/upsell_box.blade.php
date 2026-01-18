@if (! empty($upsellOffer) && ! empty($upsellOffer['rule']))
    @php
        /** @var \Modules\Cart\Entities\CartUpsellRule $rule */
        $rule    = $upsellOffer['rule'];
        $product = $upsellOffer['product'];
        
        $letCustomerChoose = (bool) ($upsellOffer['let_customer_choose'] ?? false);
        $variants = $letCustomerChoose ? ($product->variants ?? collect()) : collect();

        // 1. Find the best variant for initial display (Initial Resolve)
        $variant = $upsellOffer['variant'] ?? null;
        $allVariants = $product->variants()->withoutGlobalScope('active')->get();

        if (!$variant && $allVariants->isNotEmpty()) {
            // Priority: Default Variant > First Variant
            $variant = $allVariants->sortByDesc('is_default')->first() ?? $allVariants->first();
        }

        // 2. Resolve Image (Initial) - Strictly product-centric if letting customer choose
        $baseImageUrl = $product->base_image->exists ? $product->base_image->url : null;
        $image = $baseImageUrl;
        if (!$letCustomerChoose && $variant && $variant->base_image->exists) {
            $image = $variant->base_image->url;
        }

        // 3. Resolve Product Name (Initial) - No variant suffix if letting customer choose
        $productName = $product->name;
        if (!$letCustomerChoose && $variant && $variant->name && $variant->name !== $product->name) {
            $productName .= ' - ' . $variant->name;
        }

        // 4. Resolve Prices (Initial - Extremely Defensive with Range Support)
        $variantPrices = $allVariants->map(function($v) use ($rule) {
            $orig = (float) ($v->selling_price->amount() ?: ($v->price->amount() ?: 0));
            $upsell = 0;
            if ($orig > 0) {
                if ($rule->discount_type === 'percent') {
                    $upsell = $orig * (1 - ($rule->discount_value / 100));
                } elseif ($rule->discount_type === 'fixed') {
                    $upsell = max(0, $orig - $rule->discount_value);
                } else {
                    $upsell = $orig;
                }
            }
            return ['orig' => $orig, 'upsell' => $upsell];
        });

        if ($variantPrices->isEmpty()) {
            $pOrig = (float) ($product->selling_price->amount() ?: ($product->price->amount() ?: 0));
            $pUpsell = (float) ($upsellOffer['upsell_price'] ?? 0);
            if ($pUpsell <= 0 && $pOrig > 0) {
                 if ($rule->discount_type === 'percent') { $pUpsell = $pOrig * (1 - ($rule->discount_value / 100)); }
                 elseif ($rule->discount_type === 'fixed') { $pUpsell = max(0, $pOrig - $rule->discount_value); }
                 else { $pUpsell = $pOrig; }
            }
            $variantPrices->push(['orig' => $pOrig, 'upsell' => $pUpsell]);
        }

        $minOriginal = $variantPrices->min('orig');
        $maxOriginal = $variantPrices->max('orig');
        $minUpsell = $variantPrices->min('upsell');
        $maxUpsell = $variantPrices->max('upsell');

        $initialOriginal = $variant ? (float) ($variant->selling_price->amount() ?: $variant->price->amount()) : $minOriginal;
        $initialUpsell = $variant ? (float) ($upsellOffer['upsell_price'] ?: 0) : $minUpsell;
        
        // Final fallback if something is still 0
        if ($initialUpsell <= 0) $initialUpsell = $minUpsell;

        // 5. Finalize Discount State
        $hasDiscount = $initialUpsell < $initialOriginal && $initialOriginal > 0;
        $discountPercent = null;
        if ($hasDiscount) {
            $base = max($initialOriginal, 0.01);
            $discountPercent = (int) round(100 - ($initialUpsell * 100 / $base));
        }

        // 6. Subtitle
        $ruleSubtitle = null;
        if (is_array($rule->subtitle) && ! empty($rule->subtitle)) {
            $locale = $locale ?? locale();
            $ruleSubtitle = $rule->subtitle[$locale] ?? reset($rule->subtitle);
        } elseif (is_string($rule->subtitle)) {
            $ruleSubtitle = $rule->subtitle;
        }

        $payload = [
            'rule_id'                => (int) $rule->id,
            'product_id'             => (int) $product->id,
            'preselected_variant_id' => $rule->preselected_variant_id ? (int) $rule->preselected_variant_id : null,
            'original_price'         => (float) $initialOriginal,
            'upsell_price'           => (float) $initialUpsell,
            'min_original'           => (float) $minOriginal,
            'max_original'           => (float) $maxOriginal,
            'min_upsell'             => (float) $minUpsell,
            'max_upsell'             => (float) $maxUpsell,
            'discount_type'          => (string) $rule->discount_type,
            'discount_value'         => (float) $rule->discount_value,
            'subtitle'               => (string) $ruleSubtitle,
            'image'                  => $image,
            'base_image'             => $product->base_image->exists ? $product->base_image->url : null,
            'listing_avif_srcset'    => $product->base_image->exists ? ($product->base_image->listing_avif_srcset ?? null) : null,
            'listing_webp_srcset'    => $product->base_image->exists ? ($product->base_image->listing_webp_srcset ?? null) : null,
            'listing_jpeg_srcset'    => $product->base_image->exists ? ($product->base_image->listing_jpeg_srcset ?? null) : null,
            'thumb_webp_url'         => $product->base_image->exists ? ($product->base_image->thumb_webp_url ?? null) : null,
            'thumb_jpeg_url'         => $product->base_image->exists ? ($product->base_image->thumb_jpeg_url ?? null) : null,
            'card_webp_url'          => $product->base_image->exists ? ($product->base_image->card_webp_url ?? null) : null,
            'card_jpeg_url'          => $product->base_image->exists ? ($product->base_image->card_jpeg_url ?? null) : null,
            'product_name'           => (string) $productName,
            'base_product_name'      => (string) $product->name,
            'has_discount'           => (bool) $hasDiscount,
            'discount_percent'       => $discountPercent,
            'let_customer_choose'    => (bool) $letCustomerChoose,
            'placement'              => (string) $rule->show_on,
            'unit_decimal'           => (bool) $product->unit_decimal,
            'unit_suffix'            => (string) ($product->unit_suffix ?? ''),
            'unit_min'               => (float) ($product->unit_min ?? 1),
            'unit_step'              => (float) ($product->unit_step ?? 1),
            'variants'               => $allVariants->map(fn($v) => [
                'id' => (int) $v->id,
                'name' => (string) $v->name,
                'price' => (float) ($v->price ? $v->price->amount() : 0),
                'selling_price' => (float) ($v->getSellingPrice() ? $v->getSellingPrice()->amount() : 0),
                'qty' => (float) ($v->qty ?? 0),
                'manage_stock' => (bool) $v->manage_stock,
                'is_default' => (bool) $v->is_default,
                'image' => $v->base_image->exists ? ($v->base_image->thumb_webp_url ?? $v->base_image->card_webp_url ?? $v->base_image->thumb_jpeg_url ?? $v->base_image->url) : null,
            ])->toArray(),
            'manage_stock'           => (bool) ($variant ? $variant->manage_stock : $product->manage_stock),
            'stock_qty'              => (float) ($variant ? $variant->qty : $product->qty),
            'countdown_seconds'      => $rule->has_countdown && (float) $rule->countdown_minutes > 0
                ? max(((int) round((float) $rule->countdown_minutes)) * 60, 0)
                : null,
            'variation_label'        => (string) ($product->variations->pluck('name')->unique()->implode(' / ') ?: 'Varyant Seçin'),
        ];
    @endphp

    <div x-data="CartUpsellBox({
        offer: @json($payload),
        addUpsellUrl: '{{ route('cart.upsell.store') }}'
    })"
    class="fc-upsell-wrapper"
    x-show="show || showQuantityModal"
    x-cloak
    >
        <!-- Toast -->
        <div
            x-show="show"
            class="fc-upsell-toast-minimal"
        >
            <!-- Başlık -->
            <div class="fc-upsell-minimal-header">
                <div class="fc-upsell-minimal-title">
                    @if ($discountPercent)
                        {{ $discountPercent }}% {{ __('Özel İndirim!') }}
                    @else
                        {{ trans('storefront::upsell.default_title') }}
                    @endif
                </div>
                <button type="button" @click="reject" class="fc-upsell-minimal-close">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <!-- Countdown -->
            <div class="fc-upsell-minimal-countdown" x-show="showCountdown">
                <span x-text="'Kalan Süre: ' + countdownLabel"></span>
            </div>

            <!-- İçerik -->
            <div class="fc-upsell-minimal-content">
                <!-- Görsel ve Ürün Bilgisi -->
                <div class="fc-upsell-minimal-product">
                    <div class="fc-upsell-minimal-image">
                        <picture>
                            <template x-if="offer.listing_avif_srcset">
                                <source type="image/avif" :srcset="offer.listing_avif_srcset" sizes="100px">
                            </template>
                            <template x-if="offer.listing_webp_srcset">
                                <source type="image/webp" :srcset="offer.listing_webp_srcset" sizes="100px">
                            </template>
                            <img
                                :src="offer.thumb_webp_url || offer.card_webp_url || offer.thumb_jpeg_url || offer.image || '/assets/public/images/placeholder.png'"
                                :srcset="offer.listing_jpeg_srcset || null"
                                sizes="100px"
                                :alt="offer.product_name"
                                loading="eager"
                                fetchpriority="high"
                                decoding="async"
                                width="100"
                                height="100"
                            />
                        </picture>
                    </div>
                    <div class="fc-upsell-minimal-info">
                        <div class="fc-upsell-minimal-subtitle">DENEME KAMPANYASI</div>
                        <div class="fc-upsell-minimal-name" x-text="offer.product_name">
                            {{ $productName }}
                        </div>
                        <div class="fc-upsell-minimal-prices">
                            <template x-if="offer.has_discount || (offer.let_customer_choose && !selectedVariantId)">
                                <span class="fc-upsell-minimal-price-old" x-text="formattedOriginalPrice"></span>
                            </template>
                            <span class="fc-upsell-minimal-price-new" x-text="formattedCurrentPrice"></span>
                            <template x-if="offer.discount_percent">
                                <span class="fc-upsell-minimal-discount-badge" x-text="'-' + offer.discount_percent + '%'"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Butonlar -->
            <div class="fc-upsell-minimal-actions">
                <button
                    type="button"
                    @click="reject"
                    class="fc-upsell-minimal-btn secondary"
                >
                    Teklifi Reddet
                </button>
                <button
                    type="button"
                    @click="openQuantityModal"
                    :disabled="adding"
                    class="fc-upsell-minimal-btn primary"
                >
                    <span x-show="!adding">
                        Fırsatı Ekle
                    </span>
                    <span x-show="adding">
                        Ekleniyor...
                    </span>
                </button>
            </div>
        </div>

        <!-- Quantity Picker Modal (Bottom Sheet on Mobile) -->
        <template x-teleport="body">
            <div 
                x-show="showQuantityModal" 
                x-cloak 
                class="fc-upsell-bottom-sheet-overlay"
                @click.self="closeQuantityModal"
                @keydown.escape.window="closeQuantityModal"
            >
                <div class="fc-upsell-bottom-sheet" @click.stop>
                    <!-- Drag Handle -->
                    <div class="fc-upsell-bottom-sheet-handle"></div>
                    
                    <!-- Header -->
                    <div class="fc-upsell-bottom-sheet-header">
                        <div class="fc-upsell-bottom-sheet-header-text">
                            <h3 class="fc-upsell-bottom-sheet-title" x-text="offer.product_name"></h3>
                            <p class="fc-upsell-bottom-sheet-subtitle" x-text="modalStep === 'variant' ? 'Varyant seçimi yapın' : 'Miktar belirleyin'"></p>
                        </div>
                        <button type="button" @click="closeQuantityModal" class="fc-upsell-bottom-sheet-close">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Step: Variant Selection -->
                    <div x-show="modalStep === 'variant'" class="fc-upsell-bottom-sheet-content">
                        <div class="fc-upsell-variant-list">
                            <template x-for="v in offer.variants" :key="v.id">
                                <button 
                                    type="button" 
                                    class="fc-upsell-variant-item"
                                    :class="{ 'active': selectedVariantId === v.id }"
                                    @click="selectVariant(v)"
                                >
                                    <template x-if="v.image">
                                        <img :src="v.image" loading="eager" decoding="async" width="60" height="60" />
                                    </template>
                                    <span x-text="v.name"></span>
                                    <svg x-show="selectedVariantId === v.id" class="fc-upsell-variant-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>
                            </template>
                        </div>
                        
                        <div class="fc-upsell-bottom-sheet-actions">
                            <button type="button" @click="closeQuantityModal" class="fc-upsell-bottom-sheet-btn secondary">
                                İptal
                            </button>
                            <button type="button" @click="goToQuantityStep" class="fc-upsell-bottom-sheet-btn primary" :disabled="!selectedVariantId">
                                Devam Et
                            </button>
                        </div>
                    </div>

                    <!-- Step: Quantity Selection -->
                    <div x-show="modalStep === 'quantity'" class="fc-upsell-bottom-sheet-content">
                        <!-- Unit Decimal Quantity Picker (Same as Product Detail) -->
                        <template x-if="offer.unit_decimal">
                            <div class="decimal-quantity-card">
                                <div class="decimal-quantity-header">
                                    <div>
                                        <div class="decimal-quantity-title">
                                            <span x-text="offer.unit_label || 'Uzunluk'"></span> (<span x-text="offer.unit_suffix"></span>)
                                        </div>
                                        <div class="decimal-quantity-desc" x-text="offer.unit_info_top || 'Bu ürün metre bazında satılır. İstediğiniz ölçüyü girerek sepete ekleyebilirsiniz.'"></div>
                                    </div>
                                </div>

                                <div class="decimal-quantity-main">
                                    <button
                                        type="button"
                                        class="btn-quantity minus"
                                        :disabled="selectedQty <= (offer.unit_min || 0.5)"
                                        @click="updateQuantity(Number(selectedQty) - (offer.unit_step || 0.5))"
                                    >
                                        −
                                    </button>

                                    <div class="decimal-quantity-input">
                                        <span
                                            class="input-overlay"
                                            x-show="!isEditingQty"
                                            x-text="(offer.unit_decimal ? Number(selectedQty).toFixed(2).replace(/\.00$/, '') : selectedQty) + (offer.unit_suffix ? offer.unit_suffix : '')"
                                        ></span>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            :value="isEditingQty ? qtyInput : selectedQty"
                                            autocomplete="off"
                                            :min="offer.unit_min || 0.5"
                                            :max="offer.manage_stock ? offer.stock_qty : 999999"
                                            class="form-control input-quantity-decimal input-overlay-target"
                                            @focus="beginEditQty($event)"
                                            @blur="commitEditQty()"
                                            @input="onQtyInput($event)"
                                            @keydown.up="updateQuantity(Number(selectedQty) + (offer.unit_step || 0.5))"
                                            @keydown.down="updateQuantity(Number(selectedQty) - (offer.unit_step || 0.5))"
                                        >
                                        <span class="input-suffix" x-text="offer.unit_suffix"></span>
                                    </div>

                                    <button
                                        type="button"
                                        class="btn-quantity plus"
                                        :disabled="offer.manage_stock && (Number(selectedQty) + (offer.unit_step || 0.5)) > offer.stock_qty"
                                        @click="updateQuantity(Number(selectedQty) + (offer.unit_step || 0.5))"
                                    >
                                        +
                                    </button>
                                </div>

                                <div class="decimal-quantity-chips">
                                    <button type="button" class="chip" :disabled="offer.manage_stock && offer.stock_qty < 0.5" @click="updateQuantity(0.5)">0.5<span x-text="offer.unit_suffix"></span></button>
                                    <button type="button" class="chip" :disabled="offer.manage_stock && offer.stock_qty < 1" @click="updateQuantity(1)">1<span x-text="offer.unit_suffix"></span></button>
                                    <button type="button" class="chip" :disabled="offer.manage_stock && offer.stock_qty < 2.5" @click="updateQuantity(2.5)">2.5<span x-text="offer.unit_suffix"></span></button>
                                    <button type="button" class="chip chip--desktop-only" :disabled="offer.manage_stock && offer.stock_qty < 5" @click="updateQuantity(5)">5<span x-text="offer.unit_suffix"></span></button>
                                    <button type="button" class="chip" :disabled="offer.manage_stock && offer.stock_qty < 10" @click="updateQuantity(10)">10<span x-text="offer.unit_suffix"></span></button>
                                </div>

                                <div class="decimal-quantity-info" x-text="offer.unit_info_bottom || `Minimum kesim: ${new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(offer.unit_min || 0.5)} ${offer.unit_suffix || ''}`"></div>
                            </div>
                        </template>

                        <!-- Regular Number Quantity Picker (Same as Product Detail) -->
                        <template x-if="!offer.unit_decimal">
                            <div class="number-picker-lg">
                                <label for="upsell-qty">{{ trans('storefront::product.quantity') }}</label>

                                <div class="input-group-quantity">
                                    <input
                                        type="text"
                                        :value="selectedQty"
                                        autocomplete="off"
                                        :min="offer.unit_min || 1"
                                        :max="offer.manage_stock ? offer.stock_qty : 999999"
                                        id="upsell-qty"
                                        class="form-control input-number input-quantity"
                                        @focus="$event.target.select()"
                                        @input="updateQuantity(Number($event.target.value))"
                                        @keydown.up="updateQuantity(Number(selectedQty) + (offer.unit_step || 1))"
                                        @keydown.down="updateQuantity(Number(selectedQty) - (offer.unit_step || 1))"
                                    >

                                    <span class="btn-wrapper">
                                        <button
                                            type="button"
                                            aria-label="quantity"
                                            class="btn btn-number btn-plus"
                                            :disabled="offer.manage_stock && (Number(selectedQty) + (offer.unit_step || 1)) > offer.stock_qty"
                                            @click="updateQuantity(Number(selectedQty) + (offer.unit_step || 1))"
                                        >
                                            +
                                        </button>

                                        <button
                                            type="button"
                                            aria-label="quantity"
                                            class="btn btn-number btn-minus"
                                            :disabled="selectedQty <= (offer.unit_min || 1)"
                                            @click="updateQuantity(Number(selectedQty) - (offer.unit_step || 1))"
                                        >
                                            -
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </template>

                        <!-- Price Summary -->
                        <div class="fc-upsell-bottom-sheet-summary">
                            <div class="fc-upsell-bottom-sheet-summary-row">
                                <span>Birim Fiyat</span>
                                <span x-text="formattedCurrentPrice"></span>
                            </div>
                            <div class="fc-upsell-bottom-sheet-summary-row total">
                                <span>Toplam</span>
                                <span x-text="formatCurrency((parseFloat(offer.upsell_price) || 0) * selectedQty)"></span>
                            </div>
                        </div>

                        <div class="fc-upsell-bottom-sheet-actions">
                            <button type="button" @click="closeQuantityModal" class="fc-upsell-bottom-sheet-btn secondary">
                                İptal
                            </button>
                            <button type="button" @click="add" class="fc-upsell-bottom-sheet-btn primary" :disabled="adding">
                                <span x-show="!adding">Sepete Ekle</span>
                                <span x-show="adding">Ekleniyor...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif