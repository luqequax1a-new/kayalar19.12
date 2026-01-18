<!-- Critical Style to prevent FOUC -->
<style>
    #products-filter-modal { display: none !important; opacity: 0; }
    #products-filter-modal.in, #products-filter-modal.show { opacity: 1; }
</style>

<div class="modal fade" id="products-filter-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content ikas-filter-modal">
            <div class="modal-header">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <h4 class="modal-title" style="margin:0;">Filtre</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin:0; opacity:1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <form id="product-filters" class="ikas-filter-form" autocomplete="off">
                    <div class="form-group">
                        <label for="filter-brand">Marka</label>
                        <select name="brand_id" id="filter-brand" class="form-control">
                            <option value="">Tümü</option>
                            @isset($brands)
                                @foreach ($brands as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="filter-category">Kategori</label>
                        <select name="category_id" id="filter-category" class="form-control">
                            <option value="">Tümü</option>
                            @isset($categories)
                                @foreach ($categories as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="filter-stock">Stok</label>
                        <select name="stock" id="filter-stock" class="form-control">
                            <option value="">Tümü</option>
                            <option value="in">Stokta</option>
                            <option value="out">Stok Yok</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="modal-footer ikas-filter-modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
                <button type="button" class="btn btn-default" id="btn-clear-filters-modal">Temizle</button>
                <button type="button" class="btn btn-primary" id="btn-apply-filters-modal">Uygula</button>
            </div>
        </div>
    </div>
</div>
