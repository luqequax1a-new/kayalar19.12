<div class="dashboard-panel dashboard-insights-hub">
    <div class="insights-hub-header">
        <div class="insights-hub-title">
            <i class="fa fa-chart-bar"></i>
            <h5>Raporlar</h5>
        </div>
    </div>

    <div class="dashboard-insights-tabs" data-insights-tabs>
        @hasAccess('admin.products.index')
            <a href="#" class="tab active" data-tab="top-products">
                En Çok Satanlar
            </a>
        @endHasAccess
        
        @hasAccess('admin.users.index')
            <a href="#" class="tab" data-tab="top-customers">
                Top Müşteriler
            </a>
        @endHasAccess
        
        <a href="#" class="tab" data-tab="searches">
            Aramalar
        </a>
        
        @hasAccess('admin.reviews.index')
            <a href="#" class="tab" data-tab="reviews">
                Yorumlar
            </a>
        @endHasAccess
        
        @hasAccess('admin.products.index')
            <a href="#" class="tab" data-tab="low-stock">
                Kritik Stoklar
            </a>
        @endHasAccess
        
        <a href="#" class="tab" data-tab="tickets">
            Destek Talepleri
        </a>
    </div>

    <div class="dashboard-insights-panels">
        {{-- Top Products Panel --}}
        @hasAccess('admin.products.index')
            <div class="insights-panel active" data-panel="top-products">
                <div class="table-responsive dashboard-top-products-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th data-top-col-title>Ürün</th>
                                <th class="text-right">Adet</th>
                                <th class="text-right">Ciro</th>
                            </tr>
                        </thead>
                        <tbody data-top-products-body data-image-placeholder-url="{{ asset('build/assets/image-placeholder.png') }}">
                            <tr>
                                <td class="empty" colspan="3">{{ trans('admin::dashboard.no_data') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endHasAccess

        {{-- Top Customers Panel --}}
        @hasAccess('admin.users.index')
            <div class="insights-panel" data-panel="top-customers">
                <div class="table-responsive anchor-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Müşteri</th>
                                <th class="text-right">Sipariş</th>
                                <th class="text-right">Toplam Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topCustomers as $customer)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.users.edit', $customer->id) }}">
                                            {{ trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')) ?: ($customer->email ?: '—') }}
                                        </a>
                                        @if (!empty($customer->email))
                                            <div class="text-muted small">{{ $customer->email }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.users.edit', $customer->id) }}">
                                            {{ (int) $customer->orders_count }}
                                        </a>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.users.edit', $customer->id) }}">
                                            {{ \Modules\Support\Money::inDefaultCurrency((float) $customer->total_spent)->format() }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty" colspan="3">{{ trans('admin::dashboard.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endHasAccess

        {{-- Latest Searches Panel --}}
        <div class="insights-panel" data-panel="searches">
            <div class="table-responsive search-terms anchor-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ trans('admin::dashboard.table.latest_searches.keyword') }}</th>
                            <th>{{ trans('admin::dashboard.table.latest_searches.results') }}</th>
                            <th>{{ trans('admin::dashboard.table.latest_searches.hits') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestSearchTerms as $latestSearchTerm)
                            <tr>
                                <td>{{ $latestSearchTerm->term }}</td>
                                <td>{{ $latestSearchTerm->results }}</td>
                                <td>{{ $latestSearchTerm->hits }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="empty" colspan="3">{{ trans('admin::dashboard.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Latest Reviews Panel --}}
        @hasAccess('admin.reviews.index')
            <div class="insights-panel" data-panel="reviews">
                <div class="table-responsive anchor-table latest-reviews">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ trans('admin::dashboard.table.latest_reviews.product') }}</th>
                                <th>{{ trans('admin::dashboard.table.customer') }}</th>
                                <th>{{ trans('admin::dashboard.table.latest_reviews.rating') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestReviews as $latestReview)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.reviews.edit', $latestReview) }}">
                                            {{ $latestReview->product->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.reviews.edit', $latestReview) }}">
                                            {{ $latestReview->reviewer_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.reviews.edit', $latestReview) }}">
                                            {{ $latestReview->rating }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty" colspan="3">{{ trans('admin::dashboard.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endHasAccess

        {{-- Low Stock Panel --}}
        @hasAccess('admin.products.index')
            <div class="insights-panel" data-panel="low-stock">
                <div class="table-responsive anchor-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th class="text-right">Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStockProducts as $row)
                                <tr>
                                    <td>
                                        <div class="ls-row">
                                            <a class="ls-thumb" href="#" data-ls-lightbox-trigger data-preview-url="{{ $row['image_preview_url'] ?? '' }}">
                                                @if (!empty($row['image_url']))
                                                    <img src="{{ $row['image_url'] }}" alt="" loading="lazy" />
                                                @else
                                                    <span class="ls-thumb-fallback"></span>
                                                @endif
                                            </a>

                                            <div class="ls-meta">
                                                <a class="ls-name" href="{{ route('admin.products.edit', $row['product_id']) }}">
                                                    {{ $row['name'] }}
                                                </a>
                                                @if (!empty($row['variant']))
                                                    <div class="text-muted small">{{ $row['variant'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="ls-stock" data-ls-stock
                                             data-product-id="{{ (int) ($row['product_id'] ?? 0) }}"
                                             data-variant-id="{{ (int) ($row['variant_id'] ?? 0) }}"
                                             data-unit-suffix="{{ $row['unit_suffix'] ?? '' }}"
                                             data-inventory-update-url="{{ route('admin.products.inventory.update', $row['product_id']) }}">
                                            <button class="ls-qty-display" type="button" data-ls-edit-open>
                                                <span class="value" data-ls-qty-text>{{ $row['qty_display'] }}</span>
                                            </button>

                                            <div class="ls-editor" data-ls-editor>
                                                <input class="ls-qty" type="number" step="0.01" min="0" value="{{ (float) ($row['qty'] ?? 0) }}" data-ls-qty-input />
                                                <button class="btn btn-primary btn-sm ls-save" type="button" data-ls-save>Kaydet</button>
                                                <button class="btn btn-default btn-sm ls-cancel" type="button" data-ls-cancel>İptal</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="empty" colspan="2">{{ trans('admin::dashboard.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="ls-lightbox" data-ls-lightbox>
                    <div class="ls-lightbox-backdrop" data-ls-lightbox-close></div>
                    <div class="ls-lightbox-dialog">
                        <button type="button" class="ls-lightbox-close" data-ls-lightbox-close>×</button>
                        <img src="" alt="" data-ls-lightbox-img />
                    </div>
                </div>
            </div>
        @endHasAccess

        {{-- Tickets Panel --}}
        <div class="insights-panel" data-panel="tickets">
            <div class="ticket-stats-minimal">
                <div class="stats-row">
                    <div class="stat-box total">
                        <div class="stat-label">Toplam</div>
                        <div class="stat-value">{{ $ticketStats['total'] }}</div>
                    </div>
                    <div class="stat-box waiting">
                        <div class="stat-label">Bekleyen</div>
                        <div class="stat-value">{{ $ticketStats['waiting'] }}</div>
                        @if($ticketStats['waiting'] > 0)
                            <div class="stat-pulse"></div>
                        @endif
                    </div>
                    <div class="stat-box open">
                        <div class="stat-label">Açık</div>
                        <div class="stat-value">{{ $ticketStats['open'] }}</div>
                    </div>
                    <div class="stat-box closed">
                        <div class="stat-label">Kapalı</div>
                        <div class="stat-value">{{ $ticketStats['closed'] }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive anchor-table">
                <table class="table ticket-categories-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Kategori</th>
                            <th class="text-center" style="width: 25%;">Talep Sayısı</th>
                            <th class="text-center" style="width: 25%;">Oran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ticketStats['categories'] as $category)
                            <tr>
                                <td>{{ $category['name'] }}</td>
                                <td class="text-center">{{ $category['count'] }}</td>
                                <td class="text-center">
                                    <span class="ticket-percentage">{{ number_format($category['percentage'], 1) }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="empty" colspan="3">{{ trans('admin::dashboard.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
