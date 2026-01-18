@php
    $isEdit = $rule && $rule->exists;
    $locale = locale();
    $currentTitle = old('title', $rule->title[$locale] ?? null);
    $currentSubtitle = old('subtitle', $rule->subtitle[$locale] ?? null);
    $currentDescription = old('description', $rule->description[$locale] ?? null);
    $currentDiscountType = old('discount_type', $rule->discount_type ?? 'none');
    $currentShowOn = old('show_on', $rule->show_on ?? 'checkout');
    $currentHideIfAlreadyInCart = old('hide_if_already_in_cart', $rule->hide_if_already_in_cart ?? 1);
    $currentExcludeDiscounted = old('exclude_discounted_products', $rule->exclude_discounted_products ?? 0);
    $currentHasCountdown = old('has_countdown', $rule->has_countdown ?? 0);
    $currentTrigger = old('trigger_type', $rule->trigger_type ?? 'product_to_product');
@endphp

<div class="ikas-wrapper">
    {{-- IKAS BLACK HEADER --}}
    <div class="ikas-header-bar">
        <div class="ikas-header-bar__left">
            <a href="{{ route('admin.cart_upsell_rules.index') }}" class="ikas-back-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="ikas-breadcrumb-wrap">
                <span class="ikas-breadcrumb">Kampanya Teklifleri</span>
                <span class="ikas-breadcrumb-separator">/</span>
                <span class="ikas-breadcrumb-current">{{ $isEdit ? ($rule->internal_name ?: 'Teklif Düzenle') : 'Yeni Kampanya Teklifi' }}</span>
            </div>
        </div>
        <div class="ikas-header-bar__right">
            <button type="submit" form="{{ $isEdit ? 'upsell-rule-edit-form' : 'upsell-rule-create-form' }}" class="ikas-save-btn">Kaydet</button>
        </div>
    </div>

    <div class="ikas-admin-content">
        {{-- RAPOR ÖZET --}}
        <div class="ikas-section-card">
            <div class="ikas-section-header">
                <h3 class="ikas-section-title">Kampanya Teklifi Raporu <span class="ikas-tag">Son 30 Gün</span></h3>
            </div>
            <div class="ikas-metric-grid">
                <div class="ikas-metric-card">
                    <div class="ikas-metric-icon">
                       <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="ikas-metric-info">
                        <span class="ikas-metric-label">Teklif Kullanılan Sipariş</span>
                        <span class="ikas-metric-value">0</span>
                    </div>
                </div>
                <div class="ikas-metric-card">
                    <div class="ikas-metric-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    </div>
                    <div class="ikas-metric-info">
                        <span class="ikas-metric-label">Sepete Eklenen Ürün</span>
                        <span class="ikas-metric-value">0</span>
                    </div>
                </div>
                <div class="ikas-metric-card highlight">
                    <div class="ikas-metric-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div class="ikas-metric-info">
                        <span class="ikas-metric-label">Yaratılan Ek Ciro</span>
                        <span class="ikas-metric-value">₺ 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="ikas-form-main">
            {{-- BAŞLIK --}}
            <div class="ikas-section-card">
                <div class="ikas-section-header">
                    <h3 class="ikas-section-title">Başlık</h3>
                </div>
                <div class="ikas-section-body">
                    <div class="ikas-input-group">
                        <label class="ikas-input-label">Kampanya Teklifi Adı (İç Ad) <span class="ikas-req-star">*</span></label>
                        <input type="text" name="internal_name" class="ikas-form-control" value="{{ old('internal_name', $rule->internal_name ?? null) }}" placeholder="ör. Sepette Kampanya Teklifi">
                        <p class="ikas-input-hint">Müşteriler bu başlığı göremez.</p>
                    </div>
                </div>
            </div>

            {{-- YERLEŞİM --}}
            <div class="ikas-section-card">
                <div class="ikas-section-header">
                    <h3 class="ikas-section-title">Teklif Sayfası</h3>
                    <p class="ikas-section-desc">Kampanya teklifinin hangi sayfada görüneceğini belirleyin.</p>
                </div>
                <div class="ikas-section-body">
                    <div class="ikas-grid-selector">
                        <label class="ikas-grid-option {{ $currentShowOn === 'checkout' ? 'selected' : '' }}">
                            <input type="radio" name="show_on" value="checkout" {{ $currentShowOn === 'checkout' ? 'checked' : '' }}>
                            <div class="ikas-grid-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                            </div>
                            <span class="ikas-grid-label">Ödeme Sayfası</span>
                            <div class="ikas-grid-check">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="4"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                        </label>
                        <label class="ikas-grid-option {{ $currentShowOn === 'post_checkout' ? 'selected' : '' }}">
                            <input type="radio" name="show_on" value="post_checkout" {{ $currentShowOn === 'post_checkout' ? 'checked' : '' }}>
                            <div class="ikas-grid-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <span class="ikas-grid-label">Ödeme Sonrası</span>
                            <div class="ikas-grid-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="4"><polyline points="20 6 9 17 4 12"/></svg></div>
                        </label>
                        <label class="ikas-grid-option {{ $currentShowOn === 'product' ? 'selected' : '' }}">
                            <input type="radio" name="show_on" value="product" {{ $currentShowOn === 'product' ? 'checked' : '' }}>
                            <div class="ikas-grid-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            </div>
                            <span class="ikas-grid-label">Ürün Sayfası</span>
                            <div class="ikas-grid-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="4"><polyline points="20 6 9 17 4 12"/></svg></div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- TEKLİF DETAY --}}
            <div class="ikas-section-card">
                <div class="ikas-section-header">
                    <h3 class="ikas-section-title">Teklifler</h3>
                </div>
                <div class="ikas-section-body">
                    {{-- 1. TEKLİF --}}
                    <div class="ikas-offer-item" data-index="0">
                        <div class="ikas-offer-header">
                            <span class="ikas-offer-number">1. Teklif</span>
                            <button type="button" class="ikas-remove-offer-btn" style="visibility:hidden;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    <line x1="10" y1="11" x2="10" y2="17"/>
                                    <line x1="14" y1="11" x2="14" y2="17"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="ikas-offer-grid">
                            <div class="ikas-offer-form">
                        <div class="ikas-input-group">
                            <label class="ikas-input-label">Kampanya Tetikleyicisi</label>
                            <div class="ikas-select-wrap">
                                <select name="trigger_type" id="trigger_type" class="ikas-form-control dropdown">
                                    <option value="all_products" {{ $currentTrigger === 'all_products' ? 'selected' : '' }}>Tüm Ürünler</option>
                                    <option value="product_to_product" {{ $currentTrigger === 'product_to_product' ? 'selected' : '' }}>Belirli Ürün</option>
                                    <option value="category_to_product" {{ $currentTrigger === 'category_to_product' ? 'selected' : '' }}>Kategori Bazlı</option>
                                </select>
                            </div>
                        </div>

                        <div id="main-product-row" style="{{ $currentTrigger !== 'product_to_product' ? 'display:none;' : '' }}">
                            <div class="ikas-input-group">
                                <label class="ikas-input-label">Tetikleyici Ürün</label>
                                <div class="ikas-search-input-wrap">
                                    <input type="text" id="main_product_search" class="ikas-form-control search" value="{{ $rule->mainProduct->name ?? '' }}" placeholder="Ürün ara..." autocomplete="off">
                                    <input type="hidden" name="main_product_id" id="main_product_id" value="{{ old('main_product_id', $rule->main_product_id ?? null) }}">
                                    <ul class="ikas-autocomplete-list" id="main_product_results"></ul>
                                </div>
                            </div>
                        </div>

                        <div id="main-category-row" style="{{ $currentTrigger !== 'category_to_product' ? 'display:none;' : '' }}">
                             <div class="ikas-input-group">
                                <label class="ikas-input-label">Tetikleyici Kategori</label>
                                <div class="ikas-select-wrap">
                                    <select name="main_category_id" class="ikas-form-control dropdown">
                                        <option value="">Seçiniz...</option>
                                        @foreach ($categories as $id => $name)
                                            <option value="{{ $id }}" {{ old('main_category_id', $rule->main_category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="ikas-sep"></div>

                        <div class="ikas-input-group">
                            <label class="ikas-input-label">Sunulacak İndirimli Ürün <span class="ikas-req-star">*</span></label>
                            <div class="ikas-search-input-wrap">
                                <input type="text" id="upsell_product_search" class="ikas-form-control search" value="{{ $rule->upsellProduct->name ?? '' }}" placeholder="Kampanya ürünü ara..." autocomplete="off">
                                <input type="hidden" name="upsell_product_id" id="upsell_product_id" value="{{ old('upsell_product_id', $rule->upsell_product_id ?? null) }}">
                                <input type="hidden" name="preselected_variant_id" id="preselected_variant_id" value="{{ old('preselected_variant_id', $rule->preselected_variant_id ?? null) }}">
                                <ul class="ikas-autocomplete-list" id="upsell_product_results"></ul>
                            </div>
                            <div id="preselected_variant_summary" class="ikas-variant-tag">
                                @if($rule->preselectedVariant)
                                    Varyant: {{ $rule->preselectedVariant->name }}
                                @else
                                    Varyant: <span style="color:#6366f1;">Ziyaretçi Seçsin</span>
                                @endif
                            </div>
                        </div>

                        <div class="ikas-input-group">
                            <label class="ikas-input-label">İndirim Oranı<span class="ikas-req-star">*</span></label>
                            <div class="ikas-discount-compact">
                                <div class="ikas-discount-value">
                                    <span class="ikas-discount-symbol">%</span>
                                    <input type="number" step="0.01" name="discount_value" id="discount_value" class="ikas-form-control calc-trigger" value="{{ old('discount_value', $rule->discount_value ? number_format((float)$rule->discount_value, 2, '.', '') : null) }}" placeholder="50,00">
                                </div>
                                <div class="ikas-select-wrap">
                                    <select name="discount_type" id="discount_type" class="ikas-form-control dropdown calc-trigger">
                                        <option value="percent" {{ $currentDiscountType === 'percent' ? 'selected' : '' }}>Yüzdelik</option>
                                        <option value="fixed" {{ $currentDiscountType === 'fixed' ? 'selected' : '' }}>Sabit Tutar</option>
                                        <option value="none" {{ $currentDiscountType === 'none' ? 'selected' : '' }}>İndirim Yok</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ikas-input-group">
                            <label class="ikas-input-label">İndirim Hangi Fiyat Üzerinden Uygulanacak?</label>
                            <div class="ikas-select-wrap">
                                <select name="discount_base_price" id="discount_base_price" class="ikas-form-control dropdown">
                                    <option value="special_price" {{ old('discount_base_price', $rule->discount_base_price ?? 'special_price') === 'special_price' ? 'selected' : '' }}>İndirimli Fiyat (Special Price)</option>
                                    <option value="normal_price" {{ old('discount_base_price', $rule->discount_base_price) === 'normal_price' ? 'selected' : '' }}>Normal Fiyat (Price)</option>
                                </select>
                            </div>
                            <p class="ikas-input-hint">İndirim hesaplaması hangi fiyat üzerinden yapılacak?</p>
                        </div>

                        <div class="ikas-input-group">
                            <label class="ikas-input-label">Kampanya Teklif Başlığı (Müşteriye Görünür)</label>
                            <input type="text" name="subtitle" id="subtitle_input" class="ikas-form-control" value="{{ $currentSubtitle }}" placeholder="ör. Duck Kumaşta Anlık Fırsat İndirimi !">
                        </div>

                                <div class="ikas-input-group">
                                    <label class="ikas-input-label">Yönetici Notu / Kampanya Özeti</label>
                                    <textarea name="description" class="ikas-form-control" style="height: 80px; padding: 12px;" placeholder="Sadece panelde görünür...">{{ $currentDescription }}</textarea>
                                </div>
                            </div>

                            {{-- 1. TEKLİF ÖNİZLEMESİ --}}
                            <div class="ikas-offer-preview">
                                <div class="ikas-offer-preview-label">Önizleme</div>
                                <div class="ikas-offer-preview-card" id="main_offer_preview">
                                    <div class="ikas-op-img" id="main_preview_image">
                                        @php
                                            $previewImage = null;
                                            $previewImagePath = null;
                                            
                                            if ($rule->preselectedVariant && $rule->preselectedVariant->base_image) {
                                                $baseImg = $rule->preselectedVariant->base_image;
                                                $previewImagePath = is_object($baseImg) ? ($baseImg->path ?? $baseImg->url ?? null) : (is_array($baseImg) ? ($baseImg['path'] ?? $baseImg['url'] ?? null) : null);
                                            } elseif ($rule->upsellProduct && $rule->upsellProduct->base_image) {
                                                $baseImg = $rule->upsellProduct->base_image;
                                                $previewImagePath = is_object($baseImg) ? ($baseImg->path ?? $baseImg->url ?? null) : (is_array($baseImg) ? ($baseImg['path'] ?? $baseImg['url'] ?? null) : null);
                                            }
                                            
                                            $previewName = 'Yeni Çizgili Kumaşlar';
                                            $previewPrice = 0;
                                            
                                            if ($rule->preselectedVariant) {
                                                $productName = $rule->upsellProduct->name ?? 'Ürün Adı';
                                                $variantName = $rule->preselectedVariant->name ?? '';
                                                $previewName = $productName . ($variantName ? ' - ' . $variantName : '');
                                                $previewPrice = $rule->preselectedVariant->selling_price ? $rule->preselectedVariant->selling_price->amount() : 0;
                                            } elseif ($rule->upsellProduct) {
                                                $previewName = $rule->upsellProduct->name ?? 'Ürün Adı';
                                                $previewPrice = $rule->upsellProduct->selling_price ? $rule->upsellProduct->selling_price->amount() : 0;
                                            }
                                            
                                            $discountedPrice = $previewPrice;
                                            if ($rule->discount_type === 'percent' && $rule->discount_value) {
                                                $discountedPrice = $previewPrice - ($previewPrice * ($rule->discount_value / 100));
                                            } elseif ($rule->discount_type === 'fixed' && $rule->discount_value) {
                                                $discountedPrice = $previewPrice - $rule->discount_value;
                                            }
                                            if ($discountedPrice < 0) $discountedPrice = 0;
                                        @endphp
                                        @if($previewImagePath)
                                            <img src="{{ $previewImagePath }}" alt="{{ $previewName }}">
                                        @else
                                            <div class="ikas-op-placeholder">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <polyline points="21 15 16 10 5 21"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ikas-op-meta">
                                        <div class="ikas-op-name" id="main_preview_name">{{ $previewName }}</div>
                                        <div class="ikas-op-sub" id="main_preview_subtitle">{{ $currentSubtitle ?: 'Teklif başlığı...' }}</div>
                                        <div class="ikas-op-price">
                                            <span class="old" id="main_preview_old_price" style="{{ $previewPrice == $discountedPrice ? 'display:none;' : '' }}">₺ {{ number_format($previewPrice, 2, ',', '.') }}</span>
                                            <span class="new" id="main_preview_new_price">₺ {{ number_format($discountedPrice, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DİNAMİK TEKLİFLER --}}
                    <div class="ikas-sep"></div>
                        <div class="ikas-offers-section">
                            <div class="ikas-offers-header">
                                <h4 class="ikas-section-subtitle">Ek Teklifler</h4>
                            </div>
                            
                            <div id="offersContainer">
                                @foreach($rule->offers ?? [] as $index => $offer)
                                    <div class="ikas-offer-item" data-index="{{ $index }}">
                                        <div class="ikas-offer-header">
                                            <span class="ikas-offer-number">{{ $index + 2 }}. Teklif</span>
                                            <button type="button" class="ikas-remove-offer-btn" onclick="removeOffer(this)">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                    <line x1="10" y1="11" x2="10" y2="17"/>
                                                    <line x1="14" y1="11" x2="14" y2="17"/>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <div class="ikas-offer-grid">
                                            <div class="ikas-offer-form">
                                                <input type="hidden" name="offers[{{ $index }}][id]" value="{{ $offer->id }}">
                                                <input type="hidden" name="offers[{{ $index }}][order]" value="{{ $index }}">
                                                
                                                <div class="ikas-input-group">
                                            <label class="ikas-input-label">Ne Zaman Gösterilsin?</label>
                                            <div class="ikas-select-wrap">
                                                <select name="offers[{{ $index }}][trigger]" class="ikas-form-control dropdown">
                                                    <option value="rejected" {{ $offer->trigger === 'rejected' ? 'selected' : '' }}>Önceki Teklif Reddedildiğinde</option>
                                                    <option value="accepted" {{ $offer->trigger === 'accepted' ? 'selected' : '' }}>Önceki Teklif Kabul Edildiğinde</option>
                                                    <option value="always" {{ $offer->trigger === 'always' ? 'selected' : '' }}>Her Zaman Göster</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="ikas-input-group">
                                            <label class="ikas-input-label">Ürün</label>
                                            <div class="ikas-search-input-wrap">
                                                <input type="text" class="ikas-form-control search offer-product-search" value="{{ $offer->product->name ?? '' }}" placeholder="Ürün ara..." autocomplete="off" data-offer-index="{{ $index }}">
                                                <input type="hidden" name="offers[{{ $index }}][product_id]" value="{{ $offer->product_id }}">
                                                <input type="hidden" name="offers[{{ $index }}][variant_id]" value="{{ $offer->variant_id }}">
                                                <ul class="ikas-autocomplete-list"></ul>
                                            </div>
                                            @if($offer->variant)
                                                <div class="ikas-variant-tag offer-variant-summary">
                                                    Varyant: {{ $offer->variant->name }}
                                                </div>
                                            @elseif($offer->product && $offer->product->has_variants)
                                                <div class="ikas-variant-tag offer-variant-summary">
                                                    Varyant: <span style="color:#6366f1;">Ziyaretçi Seçsin</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="ikas-input-group">
                                            <label class="ikas-input-label">İndirim Oranı</label>
                                            <div class="ikas-discount-compact">
                                                <div class="ikas-discount-value">
                                                    <span class="ikas-discount-symbol">%</span>
                                                    <input type="number" step="0.01" name="offers[{{ $index }}][discount_value]" class="ikas-form-control" value="{{ $offer->discount_value ? number_format((float)$offer->discount_value, 2, '.', '') : '' }}" placeholder="50,00">
                                                </div>
                                                <div class="ikas-select-wrap">
                                                    <select name="offers[{{ $index }}][discount_type]" class="ikas-form-control dropdown">
                                                        <option value="percent" {{ $offer->discount_type === 'percent' ? 'selected' : '' }}>Yüzdelik</option>
                                                        <option value="fixed" {{ $offer->discount_type === 'fixed' ? 'selected' : '' }}>Sabit Tutar</option>
                                                        <option value="none" {{ $offer->discount_type === 'none' ? 'selected' : '' }}>İndirim Yok</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="ikas-input-group">
                                            <label class="ikas-input-label">İndirim Hangi Fiyat Üzerinden?</label>
                                            <div class="ikas-select-wrap">
                                                <select name="offers[{{ $index }}][discount_base_price]" class="ikas-form-control dropdown">
                                                    <option value="special_price" {{ ($offer->discount_base_price ?? 'special_price') === 'special_price' ? 'selected' : '' }}>İndirimli Fiyat</option>
                                                    <option value="normal_price" {{ ($offer->discount_base_price) === 'normal_price' ? 'selected' : '' }}>Normal Fiyat</option>
                                                </select>
                                            </div>
                                        </div>

                                                <div class="ikas-input-group">
                                                    <label class="ikas-input-label">Teklif Başlığı</label>
                                                    <input type="text" name="offers[{{ $index }}][subtitle]" class="ikas-form-control offer-subtitle-input" value="{{ $offer->subtitle[locale()] ?? '' }}" placeholder="ör. Bir Tane Daha Al!">
                                                </div>
                                                
                                                <div class="ikas-toggle-row" style="margin-top: 16px;">
                                                    <div class="ikas-toggle-info">
                                                        <span class="ikas-toggle-title">Bu Ürün Sepetteyse Gizle</span>
                                                        <span class="ikas-toggle-hint">Bu teklif, ürün sepette varsa gösterilmez</span>
                                                    </div>
                                                    <label class="ikas-switch">
                                                        <input type="checkbox" name="offers[{{ $index }}][hide_if_in_cart]" value="1" {{ ($offer->hide_if_in_cart ?? true) ? 'checked' : '' }}>
                                                        <span class="ikas-slider"></span>
                                                    </label>
                                                </div>
                                            </div>

                                            {{-- TEKLİF ÖNİZLEMESİ --}}
                                            <div class="ikas-offer-preview">
                                                <div class="ikas-offer-preview-label">Önizleme</div>
                                                <div class="ikas-offer-preview-card">
                                                <div class="ikas-op-img">
                                                    @php
                                                        $offerImgPath = null;
                                                        if ($offer->variant && $offer->variant->base_image) {
                                                            $baseImg = $offer->variant->base_image;
                                                            $offerImgPath = is_object($baseImg) ? ($baseImg->path ?? $baseImg->url ?? null) : (is_array($baseImg) ? ($baseImg['path'] ?? $baseImg['url'] ?? null) : null);
                                                        } elseif ($offer->product && $offer->product->base_image) {
                                                            $baseImg = $offer->product->base_image;
                                                            $offerImgPath = is_object($baseImg) ? ($baseImg->path ?? $baseImg->url ?? null) : (is_array($baseImg) ? ($baseImg['path'] ?? $baseImg['url'] ?? null) : null);
                                                        }
                                                        
                                                        $offerName = $offer->product->name ?? 'Ürün Adı';
                                                        if ($offer->variant) {
                                                            $offerName .= ' - ' . ($offer->variant->name ?? '');
                                                        }
                                                        
                                                        $offerPrice = 0;
                                                        if ($offer->variant && $offer->variant->selling_price) {
                                                            // Specific variant selected
                                                            $offerPrice = $offer->variant->selling_price->amount();
                                                        } elseif ($offer->product && $offer->product->selling_price) {
                                                            // Product price (no variant or product without variants)
                                                            $offerPrice = $offer->product->selling_price->amount();
                                                        } elseif ($offer->product && $offer->product->has_variants && !$offer->variant_id) {
                                                            // Variant product but no variant selected - show first variant price as placeholder
                                                            $firstVariant = $offer->product->variants->first();
                                                            if ($firstVariant && $firstVariant->selling_price) {
                                                                $offerPrice = $firstVariant->selling_price->amount();
                                                            }
                                                        }
                                                        
                                                        $offerDiscountedPrice = $offerPrice;
                                                        if ($offer->discount_type === 'percent' && $offer->discount_value) {
                                                            $offerDiscountedPrice = $offerPrice - ($offerPrice * ($offer->discount_value / 100));
                                                        } elseif ($offer->discount_type === 'fixed' && $offer->discount_value) {
                                                            $offerDiscountedPrice = $offerPrice - $offer->discount_value;
                                                        }
                                                        if ($offerDiscountedPrice < 0) $offerDiscountedPrice = 0;
                                                    @endphp
                                                    @if($offerImgPath)
                                                        <img src="{{ $offerImgPath }}" alt="{{ $offerName }}">
                                                    @else
                                                        <div class="ikas-op-placeholder">
                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                                <polyline points="21 15 16 10 5 21"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ikas-op-meta">
                                                    <div class="ikas-op-name">{{ $offerName }}</div>
                                                    <div class="ikas-op-sub">{{ $offer->subtitle[locale()] ?? 'Teklif başlığı...' }}</div>
                                                    <div class="ikas-op-price">
                                                        <span class="old" style="{{ $offerPrice == $offerDiscountedPrice ? 'display:none;' : '' }}">₺ {{ number_format($offerPrice, 2, ',', '.') }}</span>
                                                        <span class="new">₺ {{ number_format($offerDiscountedPrice, 2, ',', '.') }}</span>
                                                    </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            {{-- TEKLİF EKLEME BUTONU --}}
                            <div class="ikas-add-offer-wrap">
                                <button type="button" class="ikas-add-offer-btn" onclick="addNewOffer()">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="16"/>
                                        <line x1="8" y1="12" x2="16" y2="12"/>
                                    </svg>
                                    Teklif Ekle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GEREKSİNİMLER KARTI --}}
            <div class="ikas-section-card">
                <div class="ikas-section-header">
                    <h3 class="ikas-section-title">Gereksinimler</h3>
                </div>
                <div class="ikas-section-body">
                    <div class="ikas-inline-row">
                        <div class="ikas-input-group fluid">
                            <label class="ikas-input-label">Minimum Sepet Tutarı</label>
                            <input type="number" step="0.01" name="min_cart_total" class="ikas-form-control" value="{{ old('min_cart_total', $rule->min_cart_total) }}" placeholder="0.00">
                        </div>
                        <div class="ikas-input-group fluid">
                            <label class="ikas-input-label">Maksimum Sepet Tutarı</label>
                            <input type="number" step="0.01" name="max_cart_total" class="ikas-form-control" value="{{ old('max_cart_total', $rule->max_cart_total) }}" placeholder="Boş bırakılabilir">
                        </div>
                    </div>
                    <div class="ikas-inline-row">
                        <div class="ikas-input-group fluid">
                            <label class="ikas-input-label">Kullanım Limiti</label>
                            <input type="number" name="usage_limit" class="ikas-form-control" value="{{ old('usage_limit', $rule->usage_limit) }}" placeholder="Sınırsız (Boş bırakın)">
                        </div>
                        <div class="ikas-input-group fluid">
                            <label class="ikas-input-label">Görüntüleme Önceliği (Sort Order)</label>
                            <input type="number" name="sort_order" class="ikas-form-control" value="{{ old('sort_order', $rule->sort_order ?? 0) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- AKTİF TARİHLER KARTI --}}
            <div class="ikas-section-card">
                <div class="ikas-section-header">
                    <h3 class="ikas-section-title">Aktif Tarihler</h3>
                    <p class="ikas-section-desc">Kampanyanın geçerli olacağı tarih aralığını belirleyin.</p>
                </div>
                <div class="ikas-section-body">
                    <div class="ikas-inline-row">
                        <div class="ikas-input-group fluid">
                            <label class="ikas-input-label">Başlangıç Tarihi</label>
                            <input type="text" name="starts_at" class="ikas-form-control datetime-picker" data-time value="{{ old('starts_at', $rule->starts_at ? $rule->starts_at->format('Y-m-d H:i') : null) }}" placeholder="Seçiniz...">
                        </div>
                        <div class="ikas-input-group fluid">
                            <label class="ikas-input-label">Bitiş Tarihi</label>
                            <input type="text" name="ends_at" class="ikas-form-control datetime-picker" data-time value="{{ old('ends_at', $rule->ends_at ? $rule->ends_at->format('Y-m-d H:i') : null) }}" placeholder="Seçiniz...">
                        </div>
                    </div>
                </div>
            </div>

            {{-- EK AYARLAR --}}
            <div class="ikas-section-card">
                <div class="ikas-section-header">
                    <h3 class="ikas-section-title">Ek Ayarlar</h3>
                </div>
                <div class="ikas-section-body">
                    <div class="ikas-toggle-list">
                        <div class="ikas-toggle-row">
                            <div class="ikas-toggle-info">
                                <span class="ikas-toggle-title">Geri Sayım Ekle</span>
                                <span class="ikas-toggle-hint">Teklifte aciliyet sayacı gösterir.</span>
                            </div>
                            <label class="ikas-switch">
                                <input type="checkbox" name="has_countdown" id="has_countdown_toggle" value="1" {{ $currentHasCountdown ? 'checked' : '' }}>
                                <span class="ikas-slider"></span>
                            </label>
                        </div>
                        
                        <div id="countdown_minutes_row" class="ikas-input-group" style="padding: 16px 20px; background: #fff; border: 1px dashed #e2e8f0; border-radius: 14px; margin-top: -8px; {{ $currentHasCountdown ? '' : 'display:none;' }}">
                            <label class="ikas-input-label">Sayaç Süresi (Dakika)</label>
                            <input type="number" name="countdown_minutes" class="ikas-form-control" value="{{ old('countdown_minutes', $rule->countdown_minutes ?? 5) }}" style="width: 120px;">
                        </div>

                        <div class="ikas-divider-small"></div>

                        <div class="ikas-toggle-row">
                            <div class="ikas-toggle-info">
                                <span class="ikas-toggle-title">Ürün Sepette Varsa Gizle</span>
                            </div>
                            <label class="ikas-switch">
                                <input type="checkbox" name="hide_if_already_in_cart" value="1" {{ $currentHideIfAlreadyInCart ? 'checked' : '' }}>
                                <span class="ikas-slider"></span>
                            </label>
                        </div>
                        <div class="ikas-toggle-row">
                            <div class="ikas-toggle-info">
                                <span class="ikas-toggle-title">İndirimli Ürün Varsa Gizle</span>
                            </div>
                            <label class="ikas-switch">
                                <input type="checkbox" name="exclude_discounted_products" value="1" {{ $currentExcludeDiscounted ? 'checked' : '' }}>
                                <span class="ikas-slider"></span>
                            </label>
                        </div>
                        <div class="ikas-toggle-row">
                             <div class="ikas-toggle-info">
                                <span class="ikas-toggle-title">Kampanya Durumu</span>
                            </div>
                            <label class="ikas-switch">
                                <input type="checkbox" name="status" value="1" {{ old('status', $rule->status ?? 1) ? 'checked' : '' }}>
                                <span class="ikas-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- VARYANT SEÇİM MODALI --}}
<div class="modal fade" id="upsellVariantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f1f5f9;">
                <h4 class="modal-title" style="font-weight: 700;">Varyant Seçimi</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div id="variant_loading" style="text-align: center; padding: 20px; display: none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p style="margin-top: 10px; font-size: 13px; color: #64748b;">Varyantlar yükleniyor...</p>
                </div>
                <ul class="list-group" id="upsellVariantList" style="border: none; max-height: 400px; overflow-y: auto;"></ul>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 16px;">
                <button type="button" class="ikas-save-btn" style="background:#f1f5f9; color:#475569; width:100%;" data-dismiss="modal" onclick="skipVariantSelection()">Varyant Seçimini Müşteriye Bırak</button>
            </div>
        </div>
    </div>
</div>

{{-- IKAS DÜZELTİLMİŞ STYLES --}}
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* RESET & BASE */
    .ikas-wrapper { font-family: 'Inter', sans-serif; background-color: #f7f9fc; color: #1a1a1a; margin-top: -20px; margin-left: -20px; margin-right: -20px; border-radius: 0; }
    
    /* HEADER BAR */
    .ikas-header-bar {
        position: fixed; top: 0; left: 250px; right: 0; height: 64px;
        background: #000; color: #fff; display: flex; align-items: center;
        justify-content: space-between; padding: 0 32px; z-index: 10001;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: left 0.3s;
    }
    .main-sidebar[style*="display: none"] ~ .content-wrapper .ikas-header-bar,
    .sidebar-collapse .ikas-header-bar { left: 0px !important; }

    .ikas-header-bar__left { display: flex; align-items: center; gap: 16px; }
    .ikas-back-btn { color: #fff; background: rgba(255,255,255,0.15); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .ikas-breadcrumb-wrap { display: flex; align-items: center; }
    .ikas-breadcrumb { font-weight: 500; color: rgba(255,255,255,0.5); font-size: 14px; }
    .ikas-breadcrumb-separator { color: rgba(255,255,255,0.3); margin: 0 8px; }
    .ikas-breadcrumb-current { font-weight: 600; font-size: 14px; color: #fff; }
    .ikas-save-btn { background: #fff; color: #000; font-weight: 700; padding: 10px 24px; border-radius: 12px; border: none; cursor: pointer; transition: 0.2s; font-size: 14px; }

    /* CONTENT LAYOUT */
    .ikas-admin-content { padding: 96px 32px 64px; max-width: 1280px; margin: 0 auto; }
    
    /* CARDS */
    .ikas-section-card { background: #fff; border-radius: 16px; border: 1px solid #eef2f6; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .ikas-section-header { padding: 20px 24px; border-bottom: 1px solid #f8fafc; }
    .ikas-section-title { margin: 0; font-size: 17px; font-weight: 700; color: #111827; }
    .ikas-section-desc { margin: 4px 0 0; font-size: 13px; color: #64748b; }
    .ikas-section-body { padding: 24px; }

    /* METRICS */
    .ikas-metric-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; padding: 24px; }
    .ikas-metric-card { display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 16px; background: #fff; border: 1px solid #f1f5f9; }
    .ikas-metric-card.highlight { background: #f5f3ff; border-color: #e0e7ff; }
    .ikas-metric-icon { width: 44px; height: 44px; border-radius: 12px; background: #f8fafc; display: flex; align-items: center; justify-content: center; color: #64748b; }
    .ikas-metric-card.highlight .ikas-metric-icon { color: #6366f1; background: #fff; }
    .ikas-metric-label { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 2px; }
    .ikas-metric-value { font-size: 20px; font-weight: 800; color: #1e293b; }

    /* INPUTS */
    .ikas-input-group { margin-bottom: 24px; }
    .ikas-input-label { 
        display: block; 
        font-size: 14px; 
        font-weight: 600; 
        color: #334155; 
        margin-bottom: 8px;
        min-height: 20px; /* Aligns side-by-side inputs perfectly */
        display: flex;
        align-items: center;
    }
    .ikas-form-control {
        width: 100%; height: 46px; padding: 0 16px; border: 1.5px solid #e2e8f0; border-radius: 12px;
        font-size: 14px; color: #1e293b; transition: all 0.2s; outline: none; background: #fff;
        box-sizing: border-box;
    }
    .ikas-form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); }
    .ikas-select-wrap { position: relative; }
    .ikas-select-wrap::after {
        content: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        position: absolute; right: 16px; top: 15px; pointer-events: none;
    }
    .ikas-form-control.dropdown { appearance: none; padding-right: 40px; }

    /* TOGGLE */
    .ikas-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .ikas-switch input { opacity: 0; width: 0; height: 0; }
    .ikas-slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1; transition: .3s; border-radius: 24px;
    }
    .ikas-slider:before {
        position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
        background-color: white; transition: .3s; border-radius: 50%;
    }
    .ikas-switch input:checked + .ikas-slider { background-color: #4f46e5; }
    .ikas-switch input:checked + .ikas-slider:before { transform: translateX(20px); }

    .ikas-toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #f8fafc; border-radius: 14px; margin-bottom: 8px; }
    .ikas-toggle-title { display: block; font-weight: 600; font-size: 14px; }
    .ikas-toggle-hint { font-size: 12px; color: #64748b; }

    /* GRID SELECTOR */
    .ikas-grid-selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .ikas-grid-option {
        border: 2px solid #f1f5f9; border-radius: 16px; padding: 24px; cursor: pointer;
        display: flex; flex-direction: column; align-items: center; gap: 12px; transition: 0.2s;
        position: relative; background: #fff;
    }
    .ikas-grid-option.selected { border-color: #4f46e5; background: #f5f3ff; }
    .ikas-grid-option input { opacity: 0; position: absolute; }
    .ikas-grid-label { font-weight: 700; font-size: 14px; }
    .ikas-grid-check {
        position: absolute; top: 12px; right: 12px; width: 22px; height: 22px;
        background: #4f46e5; border-radius: 8px; opacity: 0; display: flex; align-items: center; justify-content: center;
    }
    .ikas-grid-option.selected .ikas-grid-check { opacity: 1; }

    /* PREVIEW */
    .ikas-editor-layout { display: grid; grid-template-columns: 1fr 380px; gap: 40px; }
    .ikas-p-card { border: 1.5px solid #eef2f6; border-radius: 16px; overflow: hidden; background: #fff; position: sticky; top: 100px; width: 100%; }
    .ikas-p-header { background: #f8fafc; padding: 14px; text-align: center; font-size: 12px; font-weight: 700; color: #64748b; }
    .ikas-p-timer { background: #fff1f2; color: #e11d48; padding: 8px; text-align: center; font-size: 13px; font-weight: 700; }
    .ikas-p-body { padding: 24px; }
    .ikas-p-prod { display: flex; gap: 16px; align-items: center; }
    .ikas-p-img { width: 88px; height: 88px; background: #f1f5f9; border-radius: 12px; overflow: hidden; flex-shrink: 0; aspect-ratio: 1/1; position: relative; }
    .ikas-p-img img { width: 100%; height: 100%; object-fit: cover; }
    .ikas-p-variant-badge { 
        position: absolute; 
        top: 4px; 
        right: 4px; 
        background: rgba(99, 102, 241, 0.95); 
        color: #fff; 
        padding: 2px 8px; 
        border-radius: 6px; 
        font-size: 11px; 
        font-weight: 700;
        backdrop-filter: blur(4px);
    }
    .ikas-p-meta { flex: 1; min-width: 0; }
    .ikas-p-name { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ikas-p-sub { margin: 4px 0; font-size: 12px; color: #64748b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .ikas-p-price { margin-top: 8px; display: flex; align-items: center; gap: 8px; }
    .ikas-p-price .old { color: #94a3b8; text-decoration: line-through; font-size: 13px; font-weight: 500; }
    .ikas-p-price .new { color: #000; font-size: 16px; font-weight: 800; }
    .ikas-p-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 24px; }
    .ikas-p-btn { height: 42px; border-radius: 12px; font-weight: 700; border: none; font-size: 13px; cursor: pointer; transition: 0.2s; }
    .ikas-p-btn:active { transform: scale(0.98); }
    .ikas-p-btn.dark { background: #000; color: #fff; }
    .ikas-p-btn.ghost { background: #f1f5f9; color: #475569; }

    /* DİNAMİK TEKLİFLER */
    .ikas-offers-section {
        margin-top: 24px;
    }
    .ikas-offers-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .ikas-section-subtitle {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .ikas-add-offer-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #6366f1;
        border: none;
        border-radius: 8px;
        color: #fff;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ikas-add-offer-btn:hover {
        background: #4f46e5;
        transform: translateY(-1px);
    }
    .ikas-offer-item {
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        position: relative;
    }
    .ikas-offer-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 24px;
        margin-top: 16px;
    }
    .ikas-offer-form {
        min-width: 0;
    }
    .ikas-offer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    .ikas-offer-number {
        font-size: 14px;
        font-weight: 700;
        color: #6366f1;
    }
    .ikas-remove-offer-btn {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fee2e2;
        border: none;
        border-radius: 6px;
        color: #dc2626;
        cursor: pointer;
        transition: all 0.2s;
    }
    .ikas-remove-offer-btn:hover {
        background: #fecaca;
    }

    /* TEKLİF ÖNİZLEMESİ */
    .ikas-offer-preview {
        position: sticky;
        top: 100px;
    }
    .ikas-offer-preview-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 8px;
    }
    .ikas-offer-preview-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
    }
    .ikas-op-img {
        width: 100%;
        height: 180px;
        background: #f1f5f9;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 12px;
    }
    .ikas-op-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .ikas-op-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #cbd5e1;
    }
    .ikas-op-meta {
        min-width: 0;
    }
    .ikas-op-name {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .ikas-op-sub {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 8px;
    }
    .ikas-op-price {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ikas-op-price .old {
        color: #94a3b8;
        text-decoration: line-through;
        font-size: 13px;
        font-weight: 500;
    }
    .ikas-op-price .new {
        color: #000;
        font-size: 16px;
        font-weight: 800;
    }

    /* TEKLİF EKLEME WRAP */
    .ikas-add-offer-wrap {
        text-align: center;
        padding: 20px 0;
    }

    .ikas-search-input-wrap { position: relative; }
    .ikas-autocomplete-list {
        position: absolute; width: 100%; top: 100%; left: 0; z-index: 1000;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin-top: 4px; padding: 4px;
        list-style: none; display: none;
        max-height: 350px; overflow-y: auto;
    }
    .ikas-autocomplete-list li { 
        padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 13px;
        display: flex; align-items: center; gap: 12px; transition: 0.1s;
    }
    .ikas-autocomplete-list li:hover { background: #f8fafc; color: #4f46e5; }
    .ikas-res-img { width: 40px; height: 40px; border-radius: 8px; background: #f1f5f9; object-fit: cover; flex-shrink: 0; }
    .ikas-res-info { display: flex; flex-direction: column; gap: 2px; }
    .ikas-res-name { font-weight: 600; color: #1e293b; }
    .ikas-res-sku { font-size: 11px; color: #64748b; }
    
    .ikas-sep { height: 1px; background: #f1f5f9; margin: 24px 0; }
    .ikas-divider-small { height: 1px; background: #f1f5f9; margin: 8px 20px 16px; }
    .ikas-inline-row { display: flex; gap: 20px; }
    .ikas-inline-row .fluid { flex: 1; }
    .ikas-variant-tag { font-size: 12px; font-weight: 600; background: #f1f5f9; padding: 4px 12px; border-radius: 20px; display: inline-block; margin-top: 8px; color: #4f46e5; }
    
    /* DISCOUNT COMPACT */
    .ikas-discount-compact { 
        display: flex; 
        gap: 12px; 
        align-items: flex-start;
    }
    .ikas-discount-value { 
        position: relative; 
        width: 120px;
        flex-shrink: 0;
    }
    .ikas-discount-symbol { 
        position: absolute; 
        left: 14px; 
        top: 13px;
        font-weight: 600; 
        color: #64748b; 
        font-size: 14px;
        pointer-events: none;
        z-index: 1;
    }
    .ikas-discount-value .ikas-form-control { 
        padding-left: 36px;
        height: 46px;
    }
    .ikas-discount-compact .ikas-select-wrap { 
        width: 160px;
        flex-shrink: 0;
    }
    .ikas-discount-compact .ikas-form-control {
        height: 46px;
    }
    
    /* MODAL FIX */
    .modal-backdrop { z-index: 10000; }
    #upsellVariantModal { z-index: 10001; }
    .ikas-v-item { display: flex; align-items: center; gap: 16px; padding: 12px 16px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: 0.2s; }
    .ikas-v-item:hover { background: #f8fafc; }
    .ikas-v-img { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; background: #f1f5f9; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        let currentSelectedProduct = null;
        let currentPrice = 0;

        // Form Submission Fallback
        document.querySelector('.ikas-save-btn').addEventListener('click', function(e) {
            const formId = this.getAttribute('form');
            if (formId) {
                const form = document.getElementById(formId);
                if (form) {
                    form.submit();
                }
            }
        });

        // UI Helpers
        function formatMoney(amount) {
            return '₺ ' + Number(amount).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Placement Selection
        document.querySelectorAll('.ikas-grid-option').forEach(option => {
            option.addEventListener('click', function(e) {
                const radio = this.querySelector('input[type="radio"]');
                if (radio && e.target !== radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            });
        });

        document.querySelectorAll('.ikas-grid-option input').forEach(input => {
            input.addEventListener('change', function() {
                console.log('Placement changed to:', this.value);
                document.querySelectorAll('.ikas-grid-option').forEach(item => item.classList.remove('selected'));
                if(this.checked) this.closest('.ikas-grid-option').classList.add('selected');
            });
        });

        // Trigger Type Switching
        var triggerSelect = document.getElementById('trigger_type');
        triggerSelect.addEventListener('change', function() {
            var val = this.value;
            document.getElementById('main-product-row').style.display = (val === 'product_to_product') ? 'block' : 'none';
            document.getElementById('main-category-row').style.display = (val === 'category_to_product') ? 'block' : 'none';
        });

        // Countdown Toggle
        var countdownToggle = document.getElementById('has_countdown_toggle');
        countdownToggle.addEventListener('change', function() {
            document.getElementById('countdown_minutes_row').style.display = this.checked ? 'block' : 'none';
            document.getElementById('preview_countdown').style.display = this.checked ? 'block' : 'none';
            updateCountdownDisplay();
        });

        // Countdown Minutes Update
        var countdownMinutesInput = document.getElementById('countdown_minutes');
        if (countdownMinutesInput) {
            countdownMinutesInput.addEventListener('input', updateCountdownDisplay);
        }

        function updateCountdownDisplay() {
            const minutes = parseInt(document.getElementById('countdown_minutes')?.value) || 5;
            const display = document.getElementById('countdown_display');
            if (display) {
                const mins = String(minutes).padStart(2, '0');
                display.textContent = `${mins}:00`;
            }
        }

        // Initial countdown display
        updateCountdownDisplay();

        // 1. TEKLİF ÖNİZLEME GÜNCELLEMELERİ
        var mainSubtitleInput = document.getElementById('subtitle_input');
        mainSubtitleInput.addEventListener('input', function() {
            document.getElementById('main_preview_subtitle').textContent = this.value || 'Teklif başlığı...';
        });

        function updateMainPreviewPrices() {
            const discountValue = parseFloat(document.getElementById('discount_value').value) || 0;
            const discountType = document.getElementById('discount_type').value;
            const baseType = document.getElementById('discount_base_price')?.value || 'special_price';
            
            // Get correct base price from selected product/variant object
            let basePrice = 0;
            if (currentSelectedVariant) {
                basePrice = baseType === 'normal_price' ? 
                    (currentSelectedVariant.price?.amount || currentSelectedVariant.price || 0) : 
                    (currentSelectedVariant.selling_price?.amount || currentSelectedVariant.selling_price || currentSelectedVariant.price?.amount || 0);
            } else if (currentSelectedProduct) {
                basePrice = baseType === 'normal_price' ? 
                    (currentSelectedProduct.price?.amount || currentSelectedProduct.price || 0) : 
                    (currentSelectedProduct.selling_price?.amount || currentSelectedProduct.selling_price || currentSelectedProduct.price?.amount || 0);
            } else {
                basePrice = currentPrice; // Fallback to whatever was last set
            }

            let oldPrice = basePrice;
            let newPrice = basePrice;

            if (discountType === 'percent') {
                newPrice = oldPrice - (oldPrice * (discountValue / 100));
            } else if (discountType === 'fixed') {
                newPrice = oldPrice - discountValue;
            }

            if (newPrice < 0) newPrice = 0;

            document.getElementById('main_preview_old_price').textContent = formatMoney(oldPrice);
            document.getElementById('main_preview_new_price').textContent = formatMoney(newPrice);
            
            // Hide old price if no discount
            document.getElementById('main_preview_old_price').style.display = (newPrice === oldPrice) ? 'none' : 'inline';
        }

        document.querySelectorAll('.calc-trigger').forEach(el => {
            el.addEventListener('input', updateMainPreviewPrices);
            el.addEventListener('change', updateMainPreviewPrices);
        });
        
        // Add listener for base price selector
        const mainBasePriceSelect = document.getElementById('discount_base_price');
        if (mainBasePriceSelect) {
            mainBasePriceSelect.addEventListener('change', updateMainPreviewPrices);
        }

        window.updateMainPreview = function(name, imgPath, price = null) {
            const nameEl = document.getElementById('main_preview_name');
            const imgEl = document.getElementById('main_preview_image');
            
            if (nameEl) {
                const p = currentSelectedProduct || {};
                const unitSuffix = p.unit_suffix ? ` (${p.unit_suffix})` : '';
                nameEl.textContent = name + unitSuffix;
            }
            
            if (imgPath) {
                imgEl.innerHTML = `<img src="${imgPath}">`;
            } else {
                imgEl.innerHTML = `<div class="ikas-op-placeholder"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>`;
            }
            
            if (price !== null) {
                currentPrice = parseFloat(price);
                updateMainPreviewPrices();
            }
        }

        // Robust Search Logic
        function setupIkasSearch(inputId, resId, hidId, onSelectCallback) {
            var input = document.getElementById(inputId);
            var resultsWrap = document.getElementById(resId);
            var hiddenInput = document.getElementById(hidId);
            var timer = null;

            function runSearch(isInitial = false) {
                var q = input.value.trim();
                var url = '{{ route('admin.products.index') }}?limit=15';
                if (isInitial && q.length === 0) {
                    url += '&initial=1';
                } else if (q.length > 0) {
                    url += '&query=' + encodeURIComponent(q);
                } else {
                    resultsWrap.style.display = 'none';
                    return;
                }

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    resultsWrap.innerHTML = '';
                    var items = Array.isArray(data) ? data : Object.values(data);
                    if(!items.length) {
                        resultsWrap.innerHTML = '<li style="color:#94a3b8; cursor:default; padding:16px; justify-content:center;">Sonuç bulunamadı</li>';
                        resultsWrap.style.display = 'block';
                        return;
                    }
                    items.forEach(item => {
                        var li = document.createElement('li');
                        var imgPath = (item.base_image && item.base_image.path) ? item.base_image.path : '';
                        var imgHtml = imgPath ? `<img src="${imgPath}" class="ikas-res-img">` : '<div class="ikas-res-img" style="display:flex;align-items:center;justify-content:center;background:#f1f5f9;color:#94a3b8;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>';
                        li.innerHTML = imgHtml + `<div class="ikas-res-info"><span class="ikas-res-name">${item.name || 'İsimsiz'}</span><span class="ikas-res-sku">${item.sku || 'SKU Yok'}</span></div>`;
                        
                        li.addEventListener('mousedown', (e) => { 
                            e.preventDefault();
                            e.stopPropagation();
                            hiddenInput.value = item.id;
                            input.value = item.name;
                            resultsWrap.style.display = 'none';
                            if(onSelectCallback) onSelectCallback(item, item.name, imgPath);
                        });
                        resultsWrap.appendChild(li);
                    });
                    resultsWrap.style.display = 'block';
                });
            }

            input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => runSearch(), 300); });
            input.addEventListener('focus', () => { if (input.value.trim() === '') runSearch(true); else if (resultsWrap.children.length > 0) resultsWrap.style.display = 'block'; });
            input.addEventListener('blur', () => { setTimeout(() => { resultsWrap.style.display = 'none'; }, 250); });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') e.preventDefault();
            });
        }

        let currentVariantUpdateContext = null;

        window.skipVariantSelection = function() {
            if (currentVariantUpdateContext) {
                const { offerItem, variantIdInput, variantTag, product, name, imgPath } = currentVariantUpdateContext;
                
                variantIdInput.value = '';
                if (variantTag) {
                    variantTag.innerHTML = 'Varyant: <span style="color:#6366f1;">Ziyaretçi Seçsin</span>';
                }
                
                // Temel ürün adını kullan (varyant olmadan)
                const baseName = product.base_product_name || product.name;
                const baseProduct = {...product, name: baseName};
                
                if (offerItem) {
                    // Dynamic offer
                    updateOfferPreview(offerItem, baseProduct);
                } else {
                    // Main offer
                    updateMainPreview(baseName, imgPath, product.price);
                }
            } else {
                // Fallback for legacy
                var vInput = document.getElementById('preselected_variant_id');
                var vSummary = document.getElementById('preselected_variant_summary');
                vInput.value = '';
                vSummary.innerHTML = 'Varyant: <span style="color:#6366f1;">Ziyaretçi Seçsin</span>';
                
                if (currentSelectedProduct) {
                    updateMainPreview(currentSelectedProduct.name, currentSelectedProduct.base_image.path, currentSelectedProduct.price);
                }
            }
            
            if(window.jQuery) jQuery('#upsellVariantModal').modal('hide');
        };

        // Dinamik Teklif Ekleme/Silme
        let offerIndex = {{ count($rule->offers ?? []) }};

        window.addNewOffer = function() {
            const container = document.getElementById('offersContainer');
            const offerHtml = `
                <div class="ikas-offer-item" data-index="${offerIndex}">
                    <div class="ikas-offer-header">
                        <span class="ikas-offer-number">${offerIndex + 2}. Teklif</span>
                        <button type="button" class="ikas-remove-offer-btn" onclick="removeOffer(this)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="ikas-offer-grid">
                        <div class="ikas-offer-form">
                            <input type="hidden" name="offers[${offerIndex}][order]" value="${offerIndex}">
                            
                            <div class="ikas-input-group">
                                <label class="ikas-input-label">Ne Zaman Gösterilsin?</label>
                                <div class="ikas-select-wrap">
                                    <select name="offers[${offerIndex}][trigger]" class="ikas-form-control dropdown">
                                        <option value="rejected" selected>Önceki Teklif Reddedildiğinde</option>
                                        <option value="accepted">Önceki Teklif Kabul Edildiğinde</option>
                                        <option value="always">Her Zaman Göster</option>
                                    </select>
                                </div>
                            </div>

                            <div class="ikas-input-group">
                                <label class="ikas-input-label">Ürün</label>
                                <div class="ikas-search-input-wrap">
                                    <input type="text" class="ikas-form-control search offer-product-search" placeholder="Ürün ara..." autocomplete="off">
                                    <input type="hidden" name="offers[${offerIndex}][product_id]" value="">
                                    <input type="hidden" name="offers[${offerIndex}][variant_id]" value="">
                                    <ul class="ikas-autocomplete-list"></ul>
                                </div>
                            </div>

                            <div class="ikas-input-group">
                                <label class="ikas-input-label">İndirim Oranı</label>
                                <div class="ikas-discount-compact">
                                    <div class="ikas-discount-value">
                                        <span class="ikas-discount-symbol">%</span>
                                        <input type="number" step="0.01" name="offers[${offerIndex}][discount_value]" class="ikas-form-control" placeholder="50,00">
                                    </div>
                                    <div class="ikas-select-wrap">
                                        <select name="offers[${offerIndex}][discount_type]" class="ikas-form-control dropdown">
                                            <option value="percent" selected>Yüzdelik</option>
                                            <option value="fixed">Sabit Tutar</option>
                                            <option value="none">İndirim Yok</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="ikas-input-group">
                                <label class="ikas-input-label">İndirim Hangi Fiyat Üzerinden?</label>
                                <div class="ikas-select-wrap">
                                    <select name="offers[${offerIndex}][discount_base_price]" class="ikas-form-control dropdown">
                                        <option value="special_price" selected>İndirimli Fiyat</option>
                                        <option value="normal_price">Normal Fiyat</option>
                                    </select>
                                </div>
                            </div>

                            <div class="ikas-input-group">
                                <label class="ikas-input-label">Teklif Başlığı</label>
                                <input type="text" name="offers[${offerIndex}][subtitle]" class="ikas-form-control" placeholder="ör. Bir Tane Daha Al!">
                            </div>
                        </div>

                        <div class="ikas-offer-preview">
                            <div class="ikas-offer-preview-label">Önizleme</div>
                            <div class="ikas-offer-preview-card">
                                <div class="ikas-op-img">
                                    <div class="ikas-op-placeholder">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ikas-op-meta">
                                    <div class="ikas-op-name">Ürün Adı</div>
                                    <div class="ikas-op-sub">Teklif başlığı...</div>
                                    <div class="ikas-op-price">
                                        <span class="new">₺ 0,00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', offerHtml);
            
            // Yeni eklenen teklif için ürün arama başlat
            const newOffer = container.lastElementChild;
            const searchInput = newOffer.querySelector('.offer-product-search');
            const resultsList = newOffer.querySelector('.ikas-autocomplete-list');
            const productIdInput = newOffer.querySelector('input[name*="[product_id]"]');
            const variantIdInput = newOffer.querySelector('input[name*="[variant_id]"]');
            
            setupOfferProductSearch(searchInput, resultsList, productIdInput, variantIdInput);
            
            offerIndex++;
        };

        window.removeOffer = function(btn) {
            if (confirm('Bu teklifi kaldırmak istediğinizden emin misiniz?')) {
                btn.closest('.ikas-offer-item').remove();
                updateOfferNumbers();
            }
        };

        function updateOfferNumbers() {
            document.querySelectorAll('.ikas-offer-item').forEach((item, idx) => {
                item.querySelector('.ikas-offer-number').textContent = `${idx + 2}. Teklif`;
                item.dataset.index = idx;
            });
        }

        // Teklif için ürün arama fonksiyonu
        function setupOfferProductSearch(input, resultsList, productIdInput, variantIdInput) {
            let timer = null;

            function runSearch() {
                const q = input.value.trim();
                if (q.length === 0) {
                    resultsList.style.display = 'none';
                    return;
                }

                const url = '{{ route('admin.products.index') }}?limit=15&query=' + encodeURIComponent(q);
                
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    resultsList.innerHTML = '';
                    const items = Array.isArray(data) ? data : Object.values(data);
                    
                    if(!items.length) {
                        resultsList.innerHTML = '<li style="color:#94a3b8; cursor:default; padding:16px; text-align:center;">Sonuç bulunamadı</li>';
                        resultsList.style.display = 'block';
                        return;
                    }
                    
                    items.forEach(item => {
                        const li = document.createElement('li');
                        const imgPath = (item.base_image && item.base_image.path) ? item.base_image.path : '';
                        const imgHtml = imgPath ? `<img src="${imgPath}" class="ikas-res-img">` : '<div class="ikas-res-img" style="display:flex;align-items:center;justify-content:center;background:#f1f5f9;color:#94a3b8;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>';
                        const unitSuffix = item.unit_suffix ? ` <span style="color:#94a3b8; font-size:11px;">(${item.unit_suffix})</span>` : '';
                        li.innerHTML = imgHtml + `<div class="ikas-res-info"><span class="ikas-res-name">${item.name || 'İsimsiz'}${unitSuffix}</span><span class="ikas-res-sku">${item.sku || 'SKU Yok'}</span></div>`;
                        
                        li.addEventListener('mousedown', (e) => { 
                            e.preventDefault();
                            e.stopPropagation();
                            productIdInput.value = item.id;
                            variantIdInput.value = '';
                            input.value = item.name;
                            resultsList.style.display = 'none';
                            
                            // Önizlemeyi güncelle
                            const offerItem = input.closest('.ikas-offer-item');
                            
                            // Varyant tag'ini güncelle veya oluştur
                            const searchWrap = input.closest('.ikas-search-input-wrap');
                            let variantTag = searchWrap.parentElement.querySelector('.offer-variant-summary');
                            
                            // Ürünün temel adını sakla
                            const baseProductName = item.name;
                            const productWithBaseName = {...item, base_product_name: baseProductName};
                            
                            if (item.has_variants) {
                                if (!variantTag) {
                                    variantTag = document.createElement('div');
                                    variantTag.className = 'ikas-variant-tag offer-variant-summary';
                                    searchWrap.parentElement.appendChild(variantTag);
                                }
                                const unitSuffix = item.unit_suffix ? ` (${item.unit_suffix})` : '';
                                variantTag.innerHTML = 'Varyant: <span style="color:#6366f1;">Ziyaretçi Seçsin</span>' + unitSuffix;
                                variantTag.style.display = 'inline-block';
                                
                                // Varyant seçimi için modal aç
                                showOfferVariantModal(offerItem, productWithBaseName, productIdInput, variantIdInput, input);
                            } else {
                                if (variantTag) {
                                    variantTag.style.display = 'none';
                                }
                            }
                            
                            updateOfferPreview(offerItem, productWithBaseName);
                        });
                        
                        resultsList.appendChild(li);
                    });
                    resultsList.style.display = 'block';
                });
            }

            input.addEventListener('input', () => { 
                clearTimeout(timer); 
                timer = setTimeout(() => runSearch(), 300); 
            });
            
            input.addEventListener('focus', () => { 
                if (input.value.trim().length > 0 && resultsList.children.length > 0) {
                    resultsList.style.display = 'block';
                }
            });
            
            input.addEventListener('blur', () => { 
                setTimeout(() => { resultsList.style.display = 'none'; }, 250); 
            });
        }

        // Varyant modal göster
        function showOfferVariantModal(offerItem, product, productIdInput, variantIdInput, searchInput) {
            const list = document.getElementById('upsellVariantList');
            const loading = document.getElementById('variant_loading');
            list.innerHTML = '';
            loading.style.display = 'block';
            
            // Set context for skip variant button
            currentVariantUpdateContext = {
                offerItem,
                productIdInput,
                variantIdInput,
                variantTag: offerItem ? offerItem.querySelector('.offer-variant-summary') : document.getElementById('preselected_variant_summary'),
                product: {...product, base_product_name: product.base_product_name || product.name},
                name: product.name,
                imgPath: (product.base_image && product.base_image.path) ? product.base_image.path : ''
            };
            
            if(window.jQuery) jQuery('#upsellVariantModal').modal('show');
            
            fetch('{{ route('admin.products.index') }}?variants_only=1&product_id=' + product.id, { 
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } 
            })
            .then(r => r.json())
            .then(variants => {
                loading.style.display = 'none';
                variants.forEach(v => {
                    const vName = v.name || 'Varyant ' + v.id;
                    const vImg = (v.base_image && v.base_image.path) ? v.base_image.path : ((product.base_image && product.base_image.path) ? product.base_image.path : '');
                    const li = document.createElement('li');
                    li.className = 'ikas-v-item';
                    const vImgHtml = vImg ? `<img src="${vImg}" class="ikas-v-img">` : '<div class="ikas-v-img" style="display:flex;align-items:center;justify-content:center;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>';
                    li.innerHTML = vImgHtml + `<div class="ikas-v-info"><div style="font-weight:700; color:#1e293b;">${vName}</div><div style="font-size:12px; color:#64748b;">SKU: ${v.sku || 'N/A'}</div></div>`;
                    li.onclick = (e) => {
                        e.preventDefault();
                        variantIdInput.value = v.id;
                        
                        // Varyant tag'ini güncelle
                        const variantTag = currentVariantUpdateContext.variantTag;
                        if (variantTag) {
                            variantTag.innerHTML = `Varyant: <strong>${vName}</strong>`;
                        }
                        
                        // Önizlemeyi varyant bilgisiyle güncelle
                        const variantProduct = {...product, ...v, name: product.name + ' - ' + vName};
                        if (offerItem) {
                            updateOfferPreview(offerItem, variantProduct);
                        } else {
                            updateMainPreview(variantProduct.name, vImg, v.price);
                        }
                        
                        if(window.jQuery) jQuery('#upsellVariantModal').modal('hide');
                    };
                    list.appendChild(li);
                });
            });
        }

        // Předizleme güncelleme fonksiyonu
        function updateOfferPreview(offerItem, product) {
            const previewCard = offerItem.querySelector('.ikas-offer-preview-card');
            if (!previewCard) return;
            
            // Store product data for price calculations
            offerItem._productData = {
                price: parseFloat(product.price?.amount || product.price) || 0,
                selling_price: parseFloat(product.selling_price?.amount || product.selling_price) || 0
            };
            
            const imgWrap = previewCard.querySelector('.ikas-op-img');
            const nameEl = previewCard.querySelector('.ikas-op-name');
            const subEl = previewCard.querySelector('.ikas-op-sub');
            const oldPriceEl = previewCard.querySelector('.ikas-op-price .old');
            const newPriceEl = previewCard.querySelector('.ikas-op-price .new');
            
            // Resmi güncelle
            const imgPath = (product.base_image && product.base_image.path) ? product.base_image.path : '';
            if (imgPath) {
                imgWrap.innerHTML = `<img src="${imgPath}" alt="${product.name}">`;
            } else {
                imgWrap.innerHTML = `<div class="ikas-op-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>`;
            }
            
            // İsmi güncelle
            const unitSuffix = product.unit_suffix ? ` (${product.unit_suffix})` : '';
            nameEl.textContent = (product.name || 'Ürün Adı') + unitSuffix;
            
            // Fiyatı güncelle
            const discountInput = offerItem.querySelector('input[name*="[discount_value]"]');
            const discountTypeSelect = offerItem.querySelector('select[name*="[discount_type]"]');
            const subtitleInput = offerItem.querySelector('input[name*="[subtitle]"]');
            const basePriceSelect = offerItem.querySelector('select[name*="[discount_base_price]"]');
            
            if (subtitleInput) {
                subEl.textContent = subtitleInput.value || 'Teklif başlığı...';
            }
            
            updateOfferPricesFromInputs(offerItem);
            
            // İndirim değişikliklerini dinle (sadece bir kez ekle)
            if (discountInput && !discountInput.dataset.listenerAdded) {
                discountInput.addEventListener('input', () => updateOfferPricesFromInputs(offerItem));
                discountInput.dataset.listenerAdded = 'true';
            }
            if (discountTypeSelect && !discountTypeSelect.dataset.listenerAdded) {
                discountTypeSelect.addEventListener('change', () => updateOfferPricesFromInputs(offerItem));
                discountTypeSelect.dataset.listenerAdded = 'true';
            }
            if (basePriceSelect && !basePriceSelect.dataset.listenerAdded) {
                basePriceSelect.addEventListener('change', () => updateOfferPricesFromInputs(offerItem));
                basePriceSelect.dataset.listenerAdded = 'true';
            }
            if (subtitleInput && !subtitleInput.dataset.listenerAdded) {
                subtitleInput.addEventListener('input', (e) => {
                    subEl.textContent = e.target.value || 'Teklif başlığı...';
                });
                subtitleInput.dataset.listenerAdded = 'true';
            }
        }
        
        // Remove redundant updateOfferPrices


        // Mevcut teklifler için ürün arama başlat
        document.querySelectorAll('.offer-product-search').forEach(input => {
            const parent = input.closest('.ikas-search-input-wrap');
            const resultsList = parent.querySelector('.ikas-autocomplete-list');
            const productIdInput = parent.querySelector('input[name*="[product_id]"]');
            const variantIdInput = parent.querySelector('input[name*="[variant_id]"]');
            setupOfferProductSearch(input, resultsList, productIdInput, variantIdInput);
            
            // Mevcut teklifler için indirim değişikliklerini dinle
            const offerItem = input.closest('.ikas-offer-item');
            const discountInput = offerItem.querySelector('input[name*="[discount_value]"]');
            const discountTypeSelect = offerItem.querySelector('select[name*="[discount_type]"]');
            const subtitleInput = offerItem.querySelector('input[name*="[subtitle]"]');
            
            // Önizleme için event listener'lar ekle
            if (discountInput && !discountInput.dataset.listenerAdded) {
                const previewCard = offerItem.querySelector('.ikas-offer-preview-card');
                if (previewCard) {
                    // Başlangıç fiyatını al
                    const priceText = previewCard.querySelector('.ikas-op-price .new')?.textContent || '₺ 0,00';
                    const initialPrice = parseFloat(priceText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
                    
                    discountInput.addEventListener('input', () => {
                        updateOfferPricesFromInputs(offerItem);
                    });
                    discountInput.dataset.listenerAdded = 'true';
                }
            }
            
            if (discountTypeSelect && !discountTypeSelect.dataset.listenerAdded) {
                discountTypeSelect.addEventListener('change', () => {
                    updateOfferPricesFromInputs(offerItem);
                });
                discountTypeSelect.dataset.listenerAdded = 'true';
            }
            
            if (subtitleInput && !subtitleInput.dataset.listenerAdded) {
                subtitleInput.addEventListener('input', (e) => {
                    const subEl = offerItem.querySelector('.ikas-op-sub');
                    if (subEl) subEl.textContent = e.target.value || 'Teklif başlığı...';
                });
                subtitleInput.dataset.listenerAdded = 'true';
            }
            
            const basePriceSelect = offerItem.querySelector('select[name*="[discount_base_price]"]');
            if (basePriceSelect && !basePriceSelect.dataset.listenerAdded) {
                basePriceSelect.addEventListener('change', () => {
                    updateOfferPricesFromInputs(offerItem);
                });
                basePriceSelect.dataset.listenerAdded = 'true';
            }
        });
        
        // Mevcut teklifler için önizleme fiyat güncelleme
        function updateOfferPricesFromInputs(offerItem) {
            const previewCard = offerItem.querySelector('.ikas-offer-preview-card');
            if (!previewCard) return;
            
            const oldPriceEl = previewCard.querySelector('.ikas-op-price .old');
            const newPriceEl = previewCard.querySelector('.ikas-op-price .new');
            const baseType = offerItem.querySelector('select[name*="[discount_base_price]"]')?.value || 'special_price';

            // Orijinal fiyatı belirle
            let originalPrice = 0;
            const productData = offerItem._productData; // Use stored product data if available
            
            if (productData) {
                if (baseType === 'normal_price') {
                    originalPrice = productData.price;
                } else {
                    originalPrice = productData.selling_price || productData.price;
                }
            } else {
                // Fallback to reading from display if no product data yet
                if (!offerItem.dataset.originalPrice) {
                    const currentPriceText = newPriceEl?.textContent || '₺ 0,00';
                    offerItem.dataset.originalPrice = parseFloat(currentPriceText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
                }
                originalPrice = parseFloat(offerItem.dataset.originalPrice);
            }
            
            const discountValue = parseFloat(offerItem.querySelector('input[name*="[discount_value]"]')?.value) || 0;
            const discountType = offerItem.querySelector('select[name*="[discount_type]"]')?.value || 'none';
            
            let discountedPrice = originalPrice;
            
            if (discountType === 'percent' && discountValue > 0) {
                discountedPrice = originalPrice - (originalPrice * (discountValue / 100));
            } else if (discountType === 'fixed' && discountValue > 0) {
                discountedPrice = originalPrice - discountValue;
            }
            
            if (discountedPrice < 0) discountedPrice = 0;
            
            if (oldPriceEl) {
                oldPriceEl.textContent = formatMoney(originalPrice);
                oldPriceEl.style.display = (discountedPrice === originalPrice) ? 'none' : 'inline';
            }
            if (newPriceEl) {
                newPriceEl.textContent = formatMoney(discountedPrice);
            }
        }

        // Initialize Searches
        setupIkasSearch('main_product_search', 'main_product_results', 'main_product_id');
        setupIkasSearch('upsell_product_search', 'upsell_product_results', 'upsell_product_id', (p, name, imgPath) => {
            currentSelectedProduct = p;
            updateMainPreview(name, imgPath, p.price);

            var vInput = document.getElementById('preselected_variant_id');
            var vSummary = document.getElementById('preselected_variant_summary');
            vInput.value = '';
            vSummary.innerHTML = 'Varyant: Seçilmedi (Rastgele)';

            if(p.has_variants) {
                showOfferVariantModal(null, p, document.getElementById('upsell_product_id'), document.getElementById('preselected_variant_id'), document.getElementById('upsell_product_search'));
            }
        });

        // Initialize existing offers' product search
        document.querySelectorAll('.ikas-offer-item').forEach(offerItem => {
            const searchInput = offerItem.querySelector('.offer-product-search');
            const resultsList = offerItem.querySelector('.ikas-autocomplete-list');
            const productIdInput = offerItem.querySelector('input[name*="[product_id]"]');
            const variantIdInput = offerItem.querySelector('input[name*="[variant_id]"]');
            
            if (searchInput && resultsList && productIdInput && variantIdInput) {
                setupOfferProductSearch(searchInput, resultsList, productIdInput, variantIdInput);
            }
            
            // Initialize preview prices for existing offers
            const previewCard = offerItem.querySelector('.ikas-offer-preview-card');
            if (previewCard) {
                const newPriceEl = previewCard.querySelector('.ikas-op-price .new');
                if (newPriceEl) {
                    // Extract original price from preview
                    const currentPriceText = newPriceEl.textContent || '₺ 0,00';
                    const originalPrice = parseFloat(currentPriceText.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
                    
                    // Store for later use
                    if (originalPrice > 0) {
                        offerItem.dataset.originalPrice = originalPrice;
                    }
                }
            }
        });

        // Init Prices if already selected (edit mode)
        @if($rule->upsellProduct)
            currentPrice = {{ (float) ($rule->preselectedVariant ? $rule->preselectedVariant->selling_price->amount() : ($rule->upsellProduct->selling_price->amount() ?? 0)) }};
            updateMainPreviewPrices();
        @endif
        
        // Debug: Form submit listener
        const form = document.getElementById('upsell-rule-edit-form') || document.getElementById('upsell-rule-create-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const formData = new FormData(form);
                console.log('[FORM DEBUG] Submitting form with data:');
                for (let [key, value] of formData.entries()) {
                    if (key.includes('offers')) {
                        console.log(`  ${key}: ${value}`);
                    }
                }
            });
        }
    })();
</script>
@endpush
