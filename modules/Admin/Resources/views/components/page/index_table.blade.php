@section('content')
    <div class="row index-table-actions-row">
        @isset($title)
            <div class="index-table-title">
                {{ $title }}
            </div>
        @endisset
        <div class="btn-group pull-right index-table-actions">
            @if (isset($buttons, $name) && is_array($buttons))
                @foreach ($buttons as $view)
                    <a href="{{ route("admin.{$resource}.{$view}") }}" class="btn btn-primary btn-actions btn-{{ $view }}">
                        {{ trans("admin::resource.{$view}", ['resource' => $name]) }}
                    </a>
                @endforeach
            @else
                {!! $buttons ?? '' !!}
            @endif
        </div>
    </div>

    <div class="ikas-products-page-wrapper">
        <div class="ikas-products-content">
            @isset($filters)
                {{ $filters }}
            @endisset

            @isset($before_table)
                <div style="margin-bottom: 15px;">
                    {{ $before_table }}
                </div>
            @endisset

            <div class="ikas-table-container">
                <div class="ikas-table-wrapper">
                    <div class="box-body index-table" id="{{ isset($resource) ? "{$resource}-table" : '' }}" @isset($filters_form) data-filters-form="{{ $filters_form }}" @endisset>
                        @if (isset($thead))
                            @include('admin::components.table')
                        @else
                            {{ $slot }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- İkas Style Pagination -->
            <div class="ikas-pagination-wrapper">
                <div class="ikas-pagination-left">
                    <div class="ikas-pagination-info" id="ikas-pagination-info">
                        <strong>1-20</strong> / 100 Ürün
                    </div>
                    <div class="ikas-page-size-selector">
                        <label>Sayfa Adedi:</label>
                        <select id="ikas-page-size">
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                            <option value="-1">Tümü</option>
                        </select>
                    </div>
                </div>
                <div class="ikas-pagination-right">
                    <div class="ikas-pagination-pages" id="ikas-pagination-pages">
                        <!-- Pagination buttons will be rendered here by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@isset($name)
    @push('shortcuts')
        @if (isset($buttons) && is_array($buttons) && in_array('create', $buttons))
            <dl class="dl-horizontal">
                <dt><code>c</code></dt>
                <dd>{{ trans('admin::resource.create', ['resource' => $name]) }}</dd>
            </dl>
        @endif

        <dl class="dl-horizontal">
            <dt><code>Del</code></dt>
            <dd>{{ trans('admin::resource.delete', ['resource' => $name]) }}</dd>
        </dl>
    @endpush

    @push('scripts')
        <script type="module">
            @if (isset($buttons) && is_array($buttons) && in_array('create', $buttons))
                keypressAction([
                    { key: 'c', route: '{{ route("admin.{$resource}.create") }}'}
                ]);
            @endif

            Mousetrap.bind('del', function () {
                $('.btn-delete').trigger('click');
            });

            Mousetrap.bind('backspace', function () {
                $('.btn-delete').trigger('click');
            });

            @isset($resource)
                DataTable.set('#{{ $resource }}-table .table', {
                    routePrefix: '{{ str_replace('_', '-', $resource) }}',
                    routes: {
                        table: 'table',
                        edit: 'edit',
                        destroy: 'destroy',
                    }
                });
            @endisset
        </script>
    @endpush
@endisset
