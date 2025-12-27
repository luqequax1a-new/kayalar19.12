<div class="dashboard-panel dashboard-low-stock">
    <div class="grid-header">
        <h5>Kritik Stok</h5>
    </div>

    <div class="clearfix"></div>

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
