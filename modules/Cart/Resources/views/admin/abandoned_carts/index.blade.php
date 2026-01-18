@extends('admin::layout')

@section('title', 'Terk Edilmiş Sepetler')

@push('styles')
<style>
    /* Main Container - Premium Style */
    .abandoned-carts-container {
        min-height: 100vh;
        background: #f8fafc;
        font-family: 'Outfit', 'Inter', sans-serif;
        color: #1e293b;
    }

    /* Top Bar - Modern Flush */
    .top-bar {
        background: #fff;
        padding: 20px 32px;
        border-bottom: 1px solid #e2e8f0;
    }

    .page-title-section h1 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    /* Toolbar - ikas Style */
    .abandoned-toolbar {
        background: #fff;
        padding: 16px 32px;
        border-bottom: 1px solid #e2e8f0;
    }

    .toolbar-main {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .toolbar-search {
        flex: 1;
        max-width: 400px;
    }

    .search-field {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-field i.fa-search {
        position: absolute;
        left: 12px;
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
    }

    .search-field input {
        width: 100%;
        height: 36px;
        padding: 0 36px 0 36px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
        color: #0f172a;
        background: #fff;
        transition: all 0.2s;
    }

    .search-field input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .search-field input::placeholder {
        color: #94a3b8;
    }

    .search-clear {
        position: absolute;
        right: 8px;
        width: 24px;
        height: 24px;
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        border-radius: 4px;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .search-clear:hover {
        background: #f1f5f9;
        color: #64748b;
    }

    .search-field input:not(:placeholder-shown) ~ .search-clear {
        display: flex;
    }

    .toolbar-select {
        height: 36px;
        padding: 0 32px 0 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
        color: #0f172a;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L2 4h8z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }

    .toolbar-select:hover {
        border-color: #cbd5e1;
    }

    .toolbar-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .toolbar-btn {
        height: 36px;
        padding: 0 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .toolbar-btn.btn-danger {
        background: #fff;
        border-color: #fee2e2;
        color: #ef4444;
    }

    .toolbar-btn.btn-danger:hover {
        background: #fef2f2;
        border-color: #ef4444;
    }

    /* Tabs - Sleek */
    .tabs-container {
        background: #fff;
        padding: 0 32px;
        border-bottom: 1px solid #e2e8f0;
    }

    .tabs {
        display: flex;
        gap: 32px;
    }

    .tab {
        padding: 16px 4px;
        font-size: 15px;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        position: relative;
    }

    .tab:hover {
        color: #0f172a;
    }

    .tab.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        font-weight: 600;
    }

    /* Stats Section - Floating Modern Cards */
    .stats-section {
        padding: 32px;
        background: transparent;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border-color: #cbd5e1;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #94a3b8;
        opacity: 0.3;
    }

    .stat-card.accent-blue::after { background: #3b82f6; opacity: 1; }
    .stat-card.accent-emerald::after { background: #10b981; opacity: 1; }
    .stat-card.accent-amber::after { background: #f59e0b; opacity: 1; }
    .stat-card.accent-indigo::after { background: #6366f1; opacity: 1; }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .stat-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-icon-wrapper {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .accent-blue .stat-icon-wrapper { background: #eff6ff; color: #3b82f6; }
    .accent-emerald .stat-icon-wrapper { background: #ecfdf5; color: #10b981; }
    .accent-amber .stat-icon-wrapper { background: #fffbeb; color: #f59e0b; }
    .accent-indigo .stat-icon-wrapper { background: #eef2ff; color: #6366f1; }

    .stat-value {
        font-size: 32px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        letter-spacing: -0.02em;
    }

    /* Table Design - High End Flush */
    .table-section {
        padding: 0 32px 48px 32px;
    }

    .table-container-premium {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .data-table-row {
        display: flex;
        align-items: center;
        padding: 12px 24px;
        min-height: 80px;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s;
    }

    .data-table-row:hover {
        background: #f8fafc;
    }

    .data-table-row-header {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        min-height: 48px;
        padding: 0 24px;
    }

    .header-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    .data-table-cell {
        padding: 12px 8px;
    }

    /* Column Widths */
    .cell-customer { flex: 3; }
    .cell-status { flex: 1.5; }
    .cell-reminder { flex: 1; justify-content: center; }
    .cell-date { flex: 1.5; }
    .cell-total { flex: 1.5; justify-content: flex-end; }
    .cell-actions { flex: 1; justify-content: flex-end; }

    /* Badges - ikas Rectangular Style */
    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        white-space: nowrap;
        border: 1px solid transparent;
        min-width: 110px;
        font-family: inherit;
        line-height: normal;
    }

    .pill-success { 
        background: #f6fffd; 
        color: #3cc3a1; 
        border-color: #3cc3a1; 
    }
    .pill-info { 
        background: #f5f9ff; 
        color: #4a90e2; 
        border-color: #4a90e2; 
    }
    .pill-warning { 
        background: #fffaf5; 
        color: #f5a623; 
        border-color: #f5a623; 
    }
    .pill-default { 
        background: #fafafa; 
        color: #8c8c8c; 
        border-color: #d9d9d9; 
    }

    /* Reminder Column - Plain Text */
    .reminder-count {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    /* Customer Info */
    .avatar-lite {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #64748b;
        font-size: 15px;
        flex-shrink: 0;
    }

    .customer-meta {
        display: flex;
        flex-direction: column;
        margin-left: 12px;
    }

    .customer-name-text {
        font-weight: 600;
        color: #0f172a;
        font-size: 14px;
        line-height: 1.4;
    }

    .customer-email-text {
        font-size: 12px;
        color: #64748b;
    }

    .action-btns {
        display: flex;
        gap: 4px;
        justify-content: flex-end;
    }

    .btn-icon {
        width: 28px;
        height: 28px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d9d9d9;
        background: #fff;
        color: rgba(0, 0, 0, 0.45);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-icon:hover {
        border-color: #4096ff;
        color: #4096ff;
    }

    /* Cart Items Tooltip - ikas Style */
    .cart-total-cell {
        cursor: pointer;
        position: relative;
    }

    .cart-items-popover {
        position: absolute;
        z-index: 9999;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(16, 24, 40, 0.12), 0 8px 10px -6px rgba(16, 24, 40, 0.08);
        border: 1px solid #E5E7EB;
        padding: 0;
        width: 340px;
        max-width: calc(100vw - 24px);
        min-width: 260px;
        display: none;
        pointer-events: auto;
        opacity: 0;
        transform: translateY(4px);
        transition: opacity 120ms ease, transform 120ms ease;
        --arrow-left: 24px;
    }

    .cart-items-popover.active {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    .cart-items-popover::before {
        content: '';
        position: absolute;
        width: 10px;
        height: 10px;
        background: #fff;
        border: 1px solid #E5E7EB;
        transform: rotate(45deg);
        top: -6px;
        left: var(--arrow-left, 24px);
        border-right: none;
        border-bottom: none;
    }

    .cart-items-popover[data-placement="right"]::before {
        left: -6px;
        top: 18px;
        border-right: none;
        border-bottom: none;
    }

    .cart-items-popover[data-placement="left"]::before {
        right: -6px;
        top: 18px;
        border-left: none;
        border-top: none;
    }

    .cart-items-popover[data-placement="bottom"]::before {
        top: -6px;
        left: var(--arrow-left, 24px);
        border-right: none;
        border-bottom: none;
    }

    .cart-items-popover[data-placement="top"]::before {
        bottom: -6px;
        left: var(--arrow-left, 24px);
        border-left: none;
        border-top: none;
    }

    .cart-items-popover-bridge {
        position: absolute;
        z-index: 9998;
        background: transparent;
        display: none;
        pointer-events: auto;
    }

    .cart-items-tooltip-container {
        padding: 0;
        width: 100%;
    }

    .cart-items-tooltip-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-bottom: 1px solid #EEF2F6;
        background: #fff;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    .cart-items-tooltip-title {
        font-size: 13px;
        font-weight: 700;
        color: #121926;
        font-family: Inter, sans-serif;
        letter-spacing: 0.2px;
    }

    .cart-items-tooltip-count {
        font-size: 12px;
        font-weight: 600;
        color: #667085;
        font-family: Inter, sans-serif;
        background: #F7F9FA;
        border: 1px solid #EEF2F6;
        padding: 2px 8px;
        border-radius: 999px;
    }

    .cart-items-tooltip-body {
        max-height: 380px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 4px 0;
    }
    
    .cart-items-tooltip-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .cart-items-tooltip-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .cart-items-tooltip-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .cart-items-tooltip-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .cart-item-row {
        display: flex;
        gap: 12px;
        padding: 10px 14px;
        border-bottom: 1px solid #F2F4F7;
        transition: background 0.2s;
    }

    .cart-item-row:hover {
        background: #FAFBFC;
    }

    .cart-item-row:first-child {
        padding-top: 12px;
    }

    .cart-item-row:last-child {
        border-bottom: none;
        padding-bottom: 12px;
    }

    .cart-item-image {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 8px;
        overflow: hidden;
        background: #F7F9FA;
        border: 1px solid #EEF2F6;
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-image .no-image {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(0, 0, 0, 0.25);
        font-size: 16px;
    }

    .cart-item-details {
        flex: 1;
        min-width: 0;
    }

    .cart-item-name {
        font-size: 13px;
        font-weight: 650;
        color: #121926;
        margin-bottom: 0;
        font-family: Inter, sans-serif;
        white-space: normal;
        word-break: break-word;
        line-height: 1.4;
    }

    .cart-item-variant {
        font-size: 11px;
        color: #667085;
        margin-bottom: 6px;
        font-family: Inter, sans-serif;
        background: #F7F9FA;
        padding: 2px 8px;
        border-radius: 6px;
        display: inline-block;
    }

    .cart-item-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        font-family: Inter, sans-serif;
        margin-top: 4px;
    }

    .cart-item-qty {
        display: inline-flex;
        align-items: center;
        height: 22px;
        padding: 0 8px;
        border-radius: 999px;
        background: #F2F4F7;
        border: 1px solid #EAECF0;
        color: #475467;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .cart-item-price {
        display: inline-flex;
        align-items: baseline;
        gap: 6px;
        font-weight: 600;
        color: #121926;
        font-size: 13px;
    }

    .cart-item-price-label {
        font-size: 11px;
        font-weight: 600;
        color: #667085;
        letter-spacing: 0.2px;
    }

    /* Upsell Item Styles */
    .cart-item-row.upsell-item {
        background: linear-gradient(135deg, #fef3c7 0%, #fff7ed 100%);
        border-left: 3px solid #f59e0b;
        border-top: 1px solid rgba(245, 158, 11, 0.15);
        border-bottom: 1px solid rgba(245, 158, 11, 0.15);
    }

    .upsell-badge {
        display: inline-block;
        background: #fff;
        border: 1px solid rgba(245, 158, 11, 0.35);
        color: #fff;
        color: #b45309;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
        font-family: Inter, sans-serif;
    }

    .cart-item-price-original {
        color: #94a3b8;
        font-size: 11px;
        text-decoration: line-through;
        margin-right: 6px;
        font-family: Inter, sans-serif;
    }

    .cart-item-price.upsell-price {
        color: #10b981;
        font-weight: 700;
    }

    /* Pagination */
    .table-footer {
        padding: 16px 20px;
        border-top: 1px solid #e8e8e8;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafafa;
    }

    .pagination-info {
        font-size: 14px;
        color: rgba(0, 0, 0, 0.45);
        font-family: Inter, sans-serif;
    }

    .pagination {
        display: flex;
        gap: 4px;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .pagination li a,
    .pagination li span {
        min-width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        border: 1px solid #d9d9d9;
        background: #fff;
        color: rgba(0, 0, 0, 0.88);
        font-size: 14px;
        font-weight: 400;
        text-decoration: none;
        transition: all 0.2s;
        font-family: Inter, sans-serif;
    }

    .pagination li a:hover {
        border-color: #4096ff;
        color: #4096ff;
    }

    .pagination li.active a,
    .pagination li.active span {
        background: #1890ff;
        border-color: #1890ff;
        color: #fff;
    }

    .pagination li.disabled a,
    .pagination li.disabled span {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .pagination li.disabled a:hover {
        background: #fff;
        border-color: #d9d9d9;
        color: rgba(0, 0, 0, 0.88);
    }

    /* Empty State */
    .empty-state {
        padding: 60px 24px;
        text-align: center;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.25;
    }

    .empty-title {
        font-size: 16px;
        font-weight: 500;
        color: rgba(0, 0, 0, 0.88);
        margin-bottom: 8px;
        font-family: Inter, sans-serif;
    }

    .empty-text {
        font-size: 14px;
        color: rgba(0, 0, 0, 0.45);
        font-family: Inter, sans-serif;
    }

    /* Loading */
    .loading {
        padding: 60px 24px;
        text-align: center;
    }

    .spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #f0f0f0;
        border-top-color: #1890ff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 1280px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .top-bar,
        .stats-section,
        .table-section {
            padding-left: 16px;
            padding-right: 16px;
        }

        .top-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .table-controls {
            width: 100%;
            flex-direction: column;
        }

        .search-input {
            width: 100%;
        }

        .data-table-row-header {
            display: none;
        }

        .data-table-row {
            flex-direction: column;
            align-items: flex-start;
            padding: 12px;
        }

        .data-table-cell {
            width: 100%;
            padding: 6px 0;
            text-align: left !important;
        }

        .data-table-cell:before {
            content: attr(data-label);
            font-weight: 600;
            color: rgba(0, 0, 0, 0.45);
            display: block;
            margin-bottom: 4px;
            font-size: 12px;
        }

        .action-btns {
            justify-content: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="abandoned-carts-container">
    {{-- Top Bar - Modern ikas Style --}}
    <div class="top-bar">
        <div class="page-title-section">
            <h1>Terk Edilmiş Sepet Analizi</h1>
        </div>
    </div>

    {{-- Toolbar - ikas Style --}}
    <div class="abandoned-toolbar">
        <div class="toolbar-main">
            <div class="toolbar-search">
                <div class="search-field">
                    <i class="fa fa-search"></i>
                    <input type="text" id="topSearchInput" placeholder="Müşteri veya mail...">
                    <button type="button" class="search-clear">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>

            <select class="toolbar-select" id="dateRangeFilter">
                <option value="today">Bugün</option>
                <option value="yesterday">Dün</option>
                <option value="week">Bu Hafta</option>
                <option value="30" selected>Son 30 Gün</option>
                <option value="all">Tüm Zamanlar</option>
            </select>

            <button class="toolbar-btn btn-danger" id="clearDataBtn">
                <i class="fa fa-trash"></i> Veriyi Sıfırla
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs-container">
        <div class="tabs">
            <div class="tab active" data-tab="all">
                Terk Edilmiş Sepetler
            </div>
            <div class="tab" data-tab="recovered">
                Kurtarılan Satışlar
            </div>
        </div>
    </div>

    {{-- Stats Section --}}
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-card accent-amber">
                <div class="stat-header">
                    <div class="stat-info">
                        <span class="stat-label">Sepetler</span>
                        <div class="stat-value" id="stat-abandoned">{{ number_format($stats['total_abandoned']) }}</div>
                    </div>
                    <div class="stat-icon-wrapper">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card accent-emerald">
                <div class="stat-header">
                    <div class="stat-info">
                        <span class="stat-label">Kurtarılan</span>
                        <div class="stat-value" id="stat-recovered">{{ number_format($stats['total_recovered']) }}</div>
                    </div>
                    <div class="stat-icon-wrapper">
                        <i class="fa fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card accent-blue">
                <div class="stat-header">
                    <div class="stat-info">
                        <span class="stat-label">Tıklama</span>
                        <div class="stat-value" id="stat-clicked">{{ number_format($stats['clicked_carts']) }}</div>
                    </div>
                    <div class="stat-icon-wrapper">
                        <i class="fa fa-mouse-pointer"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card accent-indigo">
                <div class="stat-header">
                    <div class="stat-info">
                        <span class="stat-label">Ciro</span>
                        <div class="stat-value" id="stat-revenue">{{ $stats['recovered_revenue'] }}</div>
                    </div>
                    <div class="stat-icon-wrapper">
                        <i class="fa fa-money"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="table-section">
        <div class="table-container-premium">
            <div id="tableContent">
                <div class="loading" style="padding: 100px; text-align: center;">
                    <i class="fa fa-spinner fa-spin" style="font-size: 32px; color: #3b82f6;"></i>
                </div>
            </div>

            <div class="table-footer" id="tableFooter" style="padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; background: #f8fafc;">
                <div class="pagination-info" id="paginationInfo" style="font-size: 13px; color: #64748b; font-weight: 500;"></div>
                <div id="pagination" class="pagination-premium"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    let dataTable;

    // Initialize DataTable
    function initDataTable() {
        dataTable = $('#hiddenTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("admin.abandoned_carts.index") }}?table=true',
            columns: [
                { data: 'customer_name', name: 'customer_name' },
                { data: 'customer_email', name: 'customer_email', visible: false },
                { data: 'is_recovered', name: 'is_recovered' },
                { data: 'reminder_count', name: 'reminder_count', searchable: false },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'cart_total', name: 'cart_total', searchable: false, orderable: false },
                { data: 'cart_items_html', name: 'cart_items_html', visible: false, searchable: false, orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[3, 'desc']],
            pageLength: 25,
            dom: 't',
            drawCallback: function(settings) {
                const json = settings.json;
                if (json) {
                    renderTable(json, settings._iDisplayStart, settings._iDisplayLength);
                    if (json.stats) {
                        updateStats(json.stats);
                    }
                }
            }
        });
    }

    function updateStats(stats) {
        $('#stat-abandoned').text(new Intl.NumberFormat().format(stats.total_abandoned));
        $('#stat-recovered').text(new Intl.NumberFormat().format(stats.total_recovered));
        $('#stat-clicked').text(new Intl.NumberFormat().format(stats.clicked_carts));
        $('#stat-revenue').text(stats.recovered_revenue);
    }

    // Render table - Premium style
    function renderTable(data, start, length) {
        const container = $('#tableContent');
        
        if (!data || !data.data || data.data.length === 0) {
            container.html(`
                <div class="empty-state" style="padding: 100px 32px; text-align: center;">
                    <div class="empty-icon" style="color: #cbd5e1; font-size: 48px;"><i class="fa fa-shopping-cart"></i></div>
                    <div class="empty-title" style="font-size: 18px; margin-top: 16px; font-weight: 600;">Sepet Bulunamadı</div>
                    <div class="empty-text" style="color: #64748b;">Bu tarih aralığında veritabanında sonuç bulunmuyor.</div>
                </div>
            `);
            $('#tableFooter').hide();
            return;
        }

        let html = '<div class="data-table-wrapper">';
        
        // Header
        html += '<div class="data-table-row data-table-row-header">';
        html += '<div class="data-table-cell cell-customer"><span class="header-label">Müşteri</span></div>';
        html += '<div class="data-table-cell cell-status"><span class="header-label">Durum</span></div>';
        html += '<div class="data-table-cell cell-reminder"><span class="header-label">Hatırlatma</span></div>';
        html += '<div class="data-table-cell cell-date"><span class="header-label">Tarih</span></div>';
        html += '<div class="data-table-cell cell-total"><span class="header-label">Tutar</span></div>';
        html += '<div class="data-table-cell cell-actions"><span class="header-label">İşlem</span></div>';
        html += '</div>';

        // Rows
        data.data.forEach(cart => {
            const rawName = cart.customer_name || 'Misafir';
            const name = rawName.replace(/<small.*<\/small>/g, '').trim();
            const subName = (rawName.match(/<small.*<\/small>/g) || [])[0] || '';
            const initial = (name || 'M').charAt(0).toUpperCase();

            html += '<div class="data-table-row">';
            
            // Customer
            html += `<div class="data-table-cell cell-customer">
                <div class="customer-info" style="display: flex; align-items: center;">
                    <div class="avatar-lite">${initial}</div>
                    <div class="customer-meta">
                        <span class="customer-name-text">${name} ${subName}</span>
                        <span class="customer-email-text">${cart.customer_email || '-'}</span>
                    </div>
                </div>
            </div>`;
            
            // Status (ikas style)
            let statusHtml = cart.is_recovered;
            statusHtml = statusHtml.replace('badge-success', 'status-pill pill-success');
            statusHtml = statusHtml.replace('badge-info', 'status-pill pill-info');
            statusHtml = statusHtml.replace('badge-warning', 'status-pill pill-warning');
            statusHtml = statusHtml.replace('badge-secondary', 'status-pill pill-default');
            
            html += `<div class="data-table-cell cell-status">${statusHtml}</div>`;
            
            // Reminder (Plain Text)
            html += `<div class="data-table-cell cell-reminder">${cart.reminder_count}</div>`;
            
            // Date
            html += `<div class="data-table-cell cell-date"><span class="date-display" style="font-weight: 500; color: #475467;">${cart.updated_at}</span></div>`;
            
            // Total
            let totalVal = (cart.cart_total.match(/<strong>(.*)<\/strong>/) || [])[1] || cart.cart_total;
            let subTotal = (cart.cart_total.match(/<span.*>(.*)<\/span>/) || [])[1] || '';
            
            html += `<div class="data-table-cell cell-total cart-total-cell" style="cursor: help;" data-cart-items="${encodeURIComponent(cart.cart_items_html || '')}">
                <div style="text-align: right;">
                    <div style="font-weight: 700; color: #0f172a; font-size: 15px;">${totalVal}</div>
                    ${subTotal ? `<div style="font-size: 11px; color: #64748b; font-weight: 600; margin-top: 2px;">${subTotal}</div>` : ''}
                </div>
            </div>`;
            
            // Actions
            html += `<div class="data-table-cell cell-actions">
                <div class="action-btns" style="display: flex; gap: 8px;">
                    ${cart.actions}
                </div>
            </div>`;
            
            html += '</div>';
        });

        html += '</div>';
        container.html(html);

        updatePagination(data.recordsFiltered, start, length);
        $('#tableFooter').show();
    }

    // Update pagination - Premium style
    function updatePagination(total, start, length) {
        const currentPage = Math.floor(start / length) + 1;
        const totalPages = Math.ceil(total / length);

        $('#paginationInfo').text(`${start + 1}-${Math.min(start + length, total)} / ${total} kayıt`);

        let html = '<div class="pagination-premium" style="display: flex; gap: 8px;">';
        
        // Prev
        html += `<button class="btn-icon ${currentPage === 1 ? 'disabled' : ''}" data-page="${currentPage - 1}" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; ${currentPage === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''}">
            <i class="fa fa-chevron-left"></i>
        </button>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                const active = i === currentPage;
                html += `<button class="btn-icon ${active ? 'active' : ''}" data-page="${i}" style="width: 32px; height: 32px; border-radius: 8px; font-weight: 600; font-size: 13px; transition: all 0.2s; ${active ? 'background: #2563eb; color: #fff; border-color: #2563eb;' : 'background: #fff; color: #64748b; border: 1px solid #e2e8f0;'}">
                    ${i}
                </button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += '<span style="color: #cbd5e1; align-self: center;">...</span>';
            }
        }

        // Next
        html += `<button class="btn-icon ${currentPage === totalPages ? 'disabled' : ''}" data-page="${currentPage + 1}" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; ${currentPage === totalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''}">
            <i class="fa fa-chevron-right"></i>
        </button>`;

        html += '</div>';
        $('#pagination').html(html);
    }

    $(document).on('click', '.pagination-premium button', function(e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;
        
        const page = parseInt($(this).data('page'));
        if (page >= 1) {
            dataTable.page(page - 1).draw('page');
        }
    });

    $('#searchInput').on('keyup', function() {
        dataTable.search(this.value).draw();
    });

    $('#entriesSelect').on('change', function() {
        dataTable.page.len(parseInt(this.value)).draw();
    });

    $(document).on('click', '.send-reminder-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const cartId = btn.data('cart-id');

        if (!confirm('Hatırlatma e-postası göndermek istediğinize emin misiniz?')) return;

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/admin/abandoned-carts/' + cartId + '/send-reminder',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                alert(response.success ? response.message : 'Hata: ' + response.message);
                if (response.success) dataTable.ajax.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Bir hata oluştu!');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa fa-envelope"></i>');
            }
        });
    });

    // Cart Items Tooltip
    let popover = null;
    let popoverBridge = null;
    let isOverCell = false;
    let isOverPopover = false;
    let isOverBridge = false;
    let currentPopoverCell = null;
    let rafRepositionId = null;
    let hideTimer = null;
    const HIDE_DELAY_MS = 450;

    function createPopover() {
        if (!popover) {
            popover = $('<div class="cart-items-popover"></div>');
            $('body').append(popover);
        }
        return popover;
    }

    function createPopoverBridge() {
        if (!popoverBridge) {
            popoverBridge = $('<div class="cart-items-popover-bridge"></div>');
            $('body').append(popoverBridge);
        }

        return popoverBridge;
    }

    function showPopover(cell) {
        currentPopoverCell = cell;
        const cartItemsHtml = decodeURIComponent($(cell).data('cart-items') || '');
        
        if (!cartItemsHtml) return;

        const pop = createPopover();
        pop.html(cartItemsHtml);
        pop.addClass('active');

        const rect = cell.getBoundingClientRect();
        const popHeight = pop.outerHeight();
        const popWidth = pop.outerWidth();
        const windowHeight = $(window).height();
        const windowWidth = $(window).width();
        const scrollTop = window.scrollY;
        const scrollLeft = window.scrollX;

        // Calculate available space
        const spaceAbove = rect.top;
        const spaceBelow = windowHeight - rect.bottom;
        const spaceLeft = rect.left;
        const spaceRight = windowWidth - rect.right;

        let top, left;
        let placement = 'bottom';
        
        // Determine best position based on available space
        // Priority: Below > Above
        
        if (spaceBelow >= popHeight + 10) {
            left = rect.left + scrollLeft + (rect.width / 2) - (popWidth / 2);
            top = rect.bottom + scrollTop + 8;
            placement = 'bottom';
        } else {
            left = rect.left + scrollLeft + (rect.width / 2) - (popWidth / 2);
            top = rect.top + scrollTop - popHeight - 8;
            placement = 'top';
        }

        pop.attr('data-placement', placement);

        const arrowLeft = Math.max(16, Math.min((rect.left + scrollLeft + (rect.width / 2)) - left, popWidth - 16));
        pop.css('--arrow-left', arrowLeft + 'px');

        // Clamp tooltip within viewport vertically
        const minTop = scrollTop + 10;
        const maxTop = scrollTop + windowHeight - popHeight - 10;
        if (maxTop > minTop) {
            top = Math.min(Math.max(top, minTop), maxTop);
        } else {
            top = minTop;
        }

        // Ensure tooltip stays within viewport
        if (left + popWidth > scrollLeft + windowWidth - 10) {
            left = scrollLeft + windowWidth - popWidth - 10;
        }
        if (left < scrollLeft + 10) {
            left = scrollLeft + 10;
        }

        pop.css({
            top: Math.max(10, top) + 'px',
            left: left + 'px'
        });

        // Hover bridge to prevent flicker when moving mouse from cell to popover
        const bridge = createPopoverBridge();
        const cellLeft = rect.left + scrollLeft;
        const cellRight = rect.right + scrollLeft;
        const popLeft = left;
        const popRight = left + popWidth;
        const bridgeLeft = Math.min(cellLeft, popLeft);
        const bridgeRight = Math.max(cellRight, popRight);

        let bridgeTop = 0;
        let bridgeHeight = 0;

        if (placement === 'bottom') {
            bridgeTop = rect.bottom + scrollTop;
            bridgeHeight = Math.max(8, top - bridgeTop);
        } else if (placement === 'top') {
            const popBottom = top + popHeight;
            bridgeTop = popBottom;
            bridgeHeight = Math.max(8, (rect.top + scrollTop) - popBottom);
        }

        if (bridgeHeight > 0) {
            bridge.css({
                top: bridgeTop + 'px',
                left: bridgeLeft + 'px',
                width: (bridgeRight - bridgeLeft) + 'px',
                height: bridgeHeight + 'px',
                display: 'block'
            });
        } else {
            bridge.hide();
        }
    }

    function hidePopover() {
        // Only hide if mouse is not over cell or popover
        if (!isOverCell && !isOverPopover && !isOverBridge) {
            if (popover) {
                popover.removeClass('active');
            }
            if (popoverBridge) {
                popoverBridge.hide();
            }
            currentPopoverCell = null;
        }
    }

    function scheduleHide() {
        if (hideTimer) {
            clearTimeout(hideTimer);
        }

        hideTimer = setTimeout(function() {
            hideTimer = null;
            hidePopover();
        }, HIDE_DELAY_MS);
    }

    function cancelHide() {
        if (hideTimer) {
            clearTimeout(hideTimer);
            hideTimer = null;
        }
    }

    function scheduleReposition() {
        if (rafRepositionId) return;

        rafRepositionId = window.requestAnimationFrame(function() {
            rafRepositionId = null;

            if (!popover || !popover.hasClass('active') || !currentPopoverCell) {
                return;
            }

            if (isOverCell || isOverPopover) {
                showPopover(currentPopoverCell);
            }
        });
    }

    $(document).on('mouseenter', '.cart-total-cell', function() {
        isOverCell = true;
        cancelHide();
        showPopover(this);
    });

    $(document).on('mouseleave', '.cart-total-cell', function() {
        isOverCell = false;
        scheduleHide();
    });

    // Keep popover open when hovering over it
    $(document).on('mouseenter', '.cart-items-popover', function() {
        isOverPopover = true;
        cancelHide();
    });

    $(document).on('mouseleave', '.cart-items-popover', function() {
        isOverPopover = false;
        scheduleHide();
    });

    // Bridge area between cell and popover (prevents flicker)
    $(document).on('mouseenter', '.cart-items-popover-bridge', function() {
        isOverBridge = true;
        cancelHide();
    });

    $(document).on('mouseleave', '.cart-items-popover-bridge', function() {
        isOverBridge = false;
        scheduleHide();
    });

    $(window).on('scroll resize', function() {
        scheduleReposition();
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            isOverCell = false;
            isOverPopover = false;
            hidePopover();
        }
    });

    $(document).on('mousedown', function(e) {
        if (!popover || !popover.hasClass('active')) return;

        const target = e.target;
        const clickedInsidePopover = popover[0] && popover[0].contains(target);
        const clickedInsideBridge = popoverBridge && popoverBridge[0] && popoverBridge[0].contains(target);
        const clickedOnCell = $(target).closest('.cart-total-cell').length > 0;

        if (!clickedInsidePopover && !clickedInsideBridge && !clickedOnCell) {
            isOverCell = false;
            isOverPopover = false;
            isOverBridge = false;
            hidePopover();
        }
    });

    // Tab switching
    $('.tab').on('click', function() {
        if ($(this).hasClass('active')) return;
        
        const tab = $(this).data('tab');
        const days = $('#dateRangeFilter').val();
        
        $('.tab').removeClass('active');
        $(this).addClass('active');
        
        updateDataTable(tab, days);
    });

    // Connect top search to DataTable
    $('#topSearchInput').on('keyup', function() {
        dataTable.search(this.value).draw();
    });

    // Connect top entries select to DataTable (already combined in top-actions update or not needed if only 1 select)
    
    // Date Range Filter
    $('#dateRangeFilter').on('change', function() {
        const days = $(this).val();
        const tab = $('.tab.active').data('tab');
        
        updateDataTable(tab, days);
    });

    function updateDataTable(tab, days) {
        let url = '{{ route("admin.abandoned_carts.index") }}?table=true';
        
        if (days) {
            url += '&days=' + days;
        }
        
        if (tab === 'recovered') {
            url += '&tab=recovered';
        }
        
        dataTable.ajax.url(url).load();
    }

    // Clear Data Button
    $('#clearDataBtn').on('click', function() {
        if (!confirm('Tüm sepet verilerini (kurtarılanlar dahil) kalıcı olarak silmek istediğinize emin misiniz?')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route("admin.abandoned_carts.clear") }}',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Bir hata oluştu!');
                btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Temizle');
            }
        });
    });

    $('body').append('<table id="hiddenTable" style="display:none;"></table>');
    initDataTable();
});

function exportData() {
    alert('Dışa aktarma özelliği yakında eklenecek!');
}
</script>
@endpush
