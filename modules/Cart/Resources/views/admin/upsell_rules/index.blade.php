@extends('admin::layout')

@section('title', 'Kampanya Teklifleri')

@component('admin::components.page.header')
    @slot('title', '')
    <li class="active">Kampanya Teklifleri</li>
@endcomponent

@section('content')
    <div class="ikas-campaign-wrapper">
        {{-- HEADER --}}
        <div class="ikas-page-header">
            <div class="ikas-page-title-wrap">
                <h1 class="ikas-page-title">Kampanya Teklifleri</h1>
                <span class="ikas-info-icon" title="Kampanya teklifleri hakkında bilgi">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4"/>
                        <path d="M12 8h.01"/>
                    </svg>
                </span>
            </div>
            <a href="{{ route('admin.cart_upsell_rules.create') }}" class="ikas-primary-btn">
                Kampanya Teklifi Ekle
            </a>
        </div>

        {{-- METRİK KARTLARI --}}
        <div class="ikas-metrics-section">
            <div class="ikas-metrics-header">
                <span class="ikas-metrics-label">Son 1 Ay</span>
            </div>
            <div class="ikas-metrics-grid">
                <div class="ikas-metric-card">
                    <div class="ikas-metric-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                        </svg>
                    </div>
                    <div class="ikas-metric-content">
                        <div class="ikas-metric-label">Teklif Kullanılan Sipariş Sayısı</div>
                        <div class="ikas-metric-value">{{ $metrics['total_orders'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="ikas-metric-card">
                    <div class="ikas-metric-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                    </div>
                    <div class="ikas-metric-content">
                        <div class="ikas-metric-label">Sepete Eklenen Ürün Sayısı</div>
                        <div class="ikas-metric-value">{{ $metrics['total_added_to_cart'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="ikas-metric-card ikas-metric-highlight">
                    <div class="ikas-metric-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                        </svg>
                    </div>
                    <div class="ikas-metric-content">
                        <div class="ikas-metric-label">Yarattığı İş Ciro</div>
                        <div class="ikas-metric-value">₺ {{ number_format($metrics['total_revenue'] ?? 0, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ARAMA VE FİLTRE --}}
        <div class="ikas-search-section">
            <div class="ikas-search-input-wrap">
                <svg class="ikas-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" class="ikas-search-input" placeholder="Tabloda arama yapın" id="ikasTableSearch">
            </div>
            <div class="ikas-filter-dropdown">
                <button class="ikas-filter-btn" id="filterToggleBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="4" y1="21" x2="4" y2="14"/>
                        <line x1="4" y1="10" x2="4" y2="3"/>
                        <line x1="12" y1="21" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12" y2="3"/>
                        <line x1="20" y1="21" x2="20" y2="16"/>
                        <line x1="20" y1="12" x2="20" y2="3"/>
                        <line x1="1" y1="14" x2="7" y2="14"/>
                        <line x1="9" y1="8" x2="15" y2="8"/>
                        <line x1="17" y1="16" x2="23" y2="16"/>
                    </svg>
                </button>
                <div class="ikas-filter-menu" id="filterMenu">
                    <div class="ikas-filter-item">
                        <label class="ikas-filter-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Durum</span>
                        </label>
                        <label class="ikas-toggle-switch">
                            <input type="checkbox" checked data-column="status">
                            <span class="ikas-toggle-slider"></span>
                        </label>
                    </div>
                    <div class="ikas-filter-item">
                        <label class="ikas-filter-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                            <span>Teklif Türü</span>
                        </label>
                        <label class="ikas-toggle-switch">
                            <input type="checkbox" checked data-column="type">
                            <span class="ikas-toggle-slider"></span>
                        </label>
                    </div>
                    <div class="ikas-filter-item">
                        <label class="ikas-filter-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <span>Teklif Sayfası</span>
                        </label>
                        <label class="ikas-toggle-switch">
                            <input type="checkbox" checked data-column="page">
                            <span class="ikas-toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLO --}}
        <div class="ikas-table-container">

            <table class="ikas-table">
                <thead class="ikas-table-head">
                    <tr>
                        <th class="ikas-th">Kampanya Teklifi</th>
                        <th class="ikas-th col-status">Durum</th>
                        <th class="ikas-th col-type">Teklif Türü</th>
                        <th class="ikas-th col-page">Teklif Sayfası</th>
                        <th class="ikas-th ikas-th-actions"></th>
                    </tr>
                </thead>
                <tbody class="ikas-table-body">
                    @forelse ($rules as $rule)
                        @php
                            $product = $rule->upsellProduct;
                            $variant = $rule->preselectedVariant;
                            $thumbUrl = null;
                            
                            if ($variant && $variant->base_image) {
                                $baseImage = $variant->base_image;
                                $thumbUrl = is_array($baseImage) ? ($baseImage['thumb'] ?? $baseImage['path'] ?? null) : ($baseImage->thumb ?? $baseImage->path ?? null);
                            }
                            
                            if (!$thumbUrl && $product && $product->base_image) {
                                $baseImage = $product->base_image;
                                $thumbUrl = is_array($baseImage) ? ($baseImage['thumb'] ?? $baseImage['path'] ?? null) : ($baseImage->thumb ?? $baseImage->path ?? null);
                            }

                            $showOnText = [
                                'checkout' => 'Ödeme Sayfası',
                                'post_checkout' => 'Ödeme Sonrası',
                                'product' => 'Ürün Sayfası'
                            ][$rule->show_on] ?? 'Bilinmiyor';

                            $triggerText = 'Tüm Ürünler';
                            if ($rule->trigger_type === 'product_to_product' && $rule->mainProduct) {
                                $triggerText = 'Ürün: ' . $rule->mainProduct->name;
                            } elseif ($rule->trigger_type === 'category_to_product' && $rule->mainCategory) {
                                $triggerText = 'Kategori: ' . $rule->mainCategory->name;
                            }

                            $discountText = 'İndirim Yok';
                            if ($rule->discount_type === 'percent') {
                                $discountText = '%' . (int)$rule->discount_value . ' İndirim';
                            } elseif ($rule->discount_type === 'fixed') {
                                $discountText = format_price((float)$rule->discount_value) . ' İndirim';
                            }
                        @endphp
                        <tr class="ikas-table-row">
                            <td class="ikas-td">
                                <div class="ikas-campaign-cell">
                                    @if ($thumbUrl)
                                        <img src="{{ $thumbUrl }}" class="ikas-campaign-thumb" alt="">
                                    @else
                                        <div class="ikas-campaign-thumb ikas-thumb-placeholder">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <path d="M21 15l-5-5L5 21"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="ikas-campaign-info">
                                        <div class="ikas-campaign-name">{{ $rule->internal_name ?: ($product ? $product->name : 'İsimsiz Kampanya') }}</div>
                                        <div class="ikas-campaign-meta">
                                            <span class="ikas-meta-item">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                                    <circle cx="12" cy="7" r="4"/>
                                                </svg>
                                                {{ $rule->indirim_orani ?? '%10 İndirim' }}
                                            </span>
                                            <span class="ikas-meta-divider">•</span>
                                            <span class="ikas-meta-item">{{ $discountText }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="ikas-td col-status">
                                @if ($rule->status)
                                    <span class="ikas-badge ikas-badge-success">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        Aktif
                                    </span>
                                @else
                                    <span class="ikas-badge ikas-badge-default">Pasif</span>
                                @endif
                            </td>
                            <td class="ikas-td col-type">
                                <span class="ikas-text-secondary">{{ $triggerText }}</span>
                            </td>
                            <td class="ikas-td col-page">
                                <span class="ikas-badge ikas-badge-info">{{ $showOnText }}</span>
                            </td>
                            <td class="ikas-td ikas-td-actions">
                                <div class="ikas-actions-wrap">
                                    <a href="{{ route('admin.cart_upsell_rules.edit', $rule) }}" class="ikas-action-btn" title="Düzenle">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.cart_upsell_rules.destroy', $rule) }}" class="ikas-action-form" onsubmit="return confirm('Bu kampanya teklifini silmek istediğinizden emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ikas-action-btn ikas-action-delete" title="Sil">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="ikas-td ikas-empty-state">
                                <div class="ikas-empty-content">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 8v4"/>
                                        <path d="M12 16h.01"/>
                                    </svg>
                                    <p class="ikas-empty-text">Henli kampanya teklifi bulunmuyor</p>
                                    <a href="{{ route('admin.cart_upsell_rules.create') }}" class="ikas-primary-btn ikas-btn-sm">İlk Kampanyayı Oluştur</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rules->hasPages())
            <div class="ikas-pagination-wrap">
                {{ $rules->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* İKAS KAMPANYA TEKLİFLERİ - TAM TASARIM */
.ikas-campaign-wrapper {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #f8f9fa;
    padding: 24px;
    margin: -20px;
}

/* HEADER */
.ikas-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.ikas-page-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}

.ikas-page-title {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}

.ikas-info-icon {
    color: #94a3b8;
    cursor: help;
    display: flex;
    align-items: center;
}

.ikas-primary-btn {
    background: #6366f1;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
}

.ikas-primary-btn:hover {
    background: #4f46e5;
    color: #fff;
    text-decoration: none;
    transform: translateY(-1px);
}

.ikas-btn-sm {
    padding: 8px 16px;
    font-size: 13px;
}

/* METRİK KARTLARI */
.ikas-metrics-section {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e5e7eb;
}

.ikas-metrics-header {
    margin-bottom: 16px;
}

.ikas-metrics-label {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
}

.ikas-metrics-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.ikas-metric-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.ikas-metric-card.ikas-metric-highlight {
    background: #f5f3ff;
    border-color: #e0e7ff;
}

.ikas-metric-icon-wrap {
    width: 40px;
    height: 40px;
    background: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #6366f1;
}

.ikas-metric-highlight .ikas-metric-icon-wrap {
    background: #6366f1;
    color: #fff;
}

.ikas-metric-content {
    flex: 1;
}

.ikas-metric-label {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
    font-weight: 500;
}

.ikas-metric-value {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a1a;
}

/* ARAMA VE FİLTRE */
.ikas-search-section {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}

.ikas-search-input-wrap {
    flex: 1;
    position: relative;
}

.ikas-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}

.ikas-search-input {
    width: 100%;
    height: 44px;
    padding: 0 16px 0 44px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    transition: all 0.2s;
}

.ikas-search-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.ikas-filter-dropdown {
    position: relative;
}

.ikas-filter-btn {
    width: 44px;
    height: 44px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #64748b;
    transition: all 0.2s;
}

.ikas-filter-btn:hover {
    background: #f8f9fa;
    color: #1a1a1a;
}

.ikas-filter-btn.active {
    background: #6366f1;
    color: #fff;
    border-color: #6366f1;
}

.ikas-filter-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    padding: 8px;
    min-width: 220px;
    display: none;
    z-index: 1000;
}

.ikas-filter-menu.show {
    display: block;
}

.ikas-filter-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    border-radius: 8px;
    transition: background 0.15s;
}

.ikas-filter-item:hover {
    background: #f8f9fa;
}

.ikas-filter-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #1a1a1a;
    cursor: pointer;
    margin: 0;
}

.ikas-filter-label svg {
    color: #6366f1;
}

.ikas-toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}

.ikas-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.ikas-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: .3s;
    border-radius: 24px;
}

.ikas-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

.ikas-toggle-switch input:checked + .ikas-toggle-slider {
    background-color: #6366f1;
}

.ikas-toggle-switch input:checked + .ikas-toggle-slider:before {
    transform: translateX(20px);
}

/* TABLO */
.ikas-table-container {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.ikas-table {
    width: 100%;
    border-collapse: collapse;
}

.ikas-table-head {
    background: #f8f9fa;
    border-bottom: 1px solid #e5e7eb;
}

.ikas-th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ikas-th-actions {
    width: 80px;
    text-align: right;
}

.ikas-table-body .ikas-table-row {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}

.ikas-table-body .ikas-table-row:hover {
    background: #f8f9fa;
}

.ikas-table-body .ikas-table-row:last-child {
    border-bottom: none;
}

.ikas-td {
    padding: 16px;
    font-size: 14px;
    color: #1a1a1a;
    vertical-align: middle;
}

.ikas-td-actions {
    text-align: right;
}

/* KAMPANYA CELL */
.ikas-campaign-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ikas-campaign-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid #e5e7eb;
}

.ikas-thumb-placeholder {
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
}

.ikas-campaign-info {
    flex: 1;
    min-width: 0;
}

.ikas-campaign-name {
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ikas-campaign-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #64748b;
}

.ikas-meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.ikas-meta-divider {
    color: #cbd5e1;
}

/* BADGES */
.ikas-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.ikas-badge-success {
    background: #d1fae5;
    color: #065f46;
}

.ikas-badge-default {
    background: #f1f5f9;
    color: #64748b;
}

.ikas-badge-info {
    background: #dbeafe;
    color: #1e40af;
}

.ikas-text-secondary {
    color: #64748b;
    font-size: 13px;
}

/* ACTIONS */
.ikas-actions-wrap {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}

.ikas-action-form {
    display: inline;
}

.ikas-action-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    color: #64748b;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0;
}

.ikas-action-btn:hover {
    background: #f1f5f9;
    color: #1a1a1a;
}

.ikas-action-delete:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* EMPTY STATE */
.ikas-empty-state {
    padding: 60px 20px !important;
    text-align: center;
}

.ikas-empty-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.ikas-empty-content svg {
    color: #cbd5e1;
}

.ikas-empty-text {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

/* PAGINATION */
.ikas-pagination-wrap {
    padding: 16px;
    background: #fff;
    border-top: 1px solid #e5e7eb;
    border-radius: 0 0 12px 12px;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .ikas-metrics-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .ikas-campaign-wrapper {
        padding: 16px;
    }
    
    .ikas-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Filtre toggle
const filterBtn = document.getElementById('filterToggleBtn');
const filterMenu = document.getElementById('filterMenu');

if (filterBtn && filterMenu) {
    filterBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        filterMenu.classList.toggle('show');
        filterBtn.classList.toggle('active');
    });

    // Dışarı tıklandığında kapat
    document.addEventListener('click', function(e) {
        if (!filterMenu.contains(e.target) && e.target !== filterBtn) {
            filterMenu.classList.remove('show');
            filterBtn.classList.remove('active');
        }
    });

    // Kolon görünürlük toggle'ları
    const columnToggles = filterMenu.querySelectorAll('input[type="checkbox"]');
    columnToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const column = this.getAttribute('data-column');
            const elements = document.querySelectorAll('.col-' + column);
            elements.forEach(el => {
                el.style.display = this.checked ? '' : 'none';
            });
        });
    });
}

// Tablo arama
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ikasTableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.ikas-table-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
