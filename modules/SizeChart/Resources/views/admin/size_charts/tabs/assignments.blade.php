<div class="row">
    <div class="col-md-8">
        {{ Form::select('categories', trans('size_chart::attributes.categories'), $errors, $categories, $sizeChart, ['class' => 'selectize', 'multiple' => true]) }}

        {{ Form::select('tags', trans('size_chart::attributes.tags'), $errors, $tags, $sizeChart, ['class' => 'selectize', 'multiple' => true]) }}

        <div class="form-group">
            <label class="col-md-3 control-label text-left">{{ trans('size_chart::attributes.product_id') }}</label>
            <div class="col-md-9">
                <select
                    name="product_id"
                    class="form-control selectize size-chart-product-select"
                    data-search-url="{{ route('admin.size_charts.products') }}"
                >
                    <option value=""></option>

                    @if (!empty($sizeChart->product_id))
                        <option value="{{ $sizeChart->product_id }}" selected>
                            #{{ $sizeChart->product_id }}
                        </option>
                    @endif
                </select>
            </div>
        </div>
    </div>
</div>
