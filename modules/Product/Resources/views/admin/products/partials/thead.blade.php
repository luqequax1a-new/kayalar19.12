<tr>
    @include('admin::partials.table.select_all')

    <th class="sort-handle-col" style="width:30px; padding:4px !important; text-align:center;">≡</th>
    <th class="col-thumbnail">GÖRSEL</th>
    <th class="col-name">PRODUCT NAME</th>
    <th class="col-default-category">CATEGORY</th>
    <th class="col-brand">{{ trans('product::products.table.brand') }}</th>
    <th class="col-price">{{ trans('product::products.table.price') }}</th>
    <th class="col-stock">{{ trans('product::products.table.stock') }}</th>
    <th class="col-status">{{ trans('admin::admin.table.status') }}</th>
    <th class="col-created-at">Oluşturma</th>
    <th class="col-actions">Actions</th>
</tr>
