@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('size_chart::size_charts.size_charts'))

    <li class="active">{{ trans('size_chart::size_charts.size_charts') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'size_charts')
    @slot('name', trans('size_chart::size_charts.size_chart'))

    @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')

                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('size_chart::size_charts.table.title') }}</th>
                <th>{{ trans('size_chart::size_charts.table.type') }}</th>
                <th>{{ trans('admin::admin.table.status') }}</th>
                <th data-sort>{{ trans('admin::admin.table.updated') }}</th>
            </tr>
        @endslot
    @endcomponent
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#size_charts-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'title', name: 'translations.title', orderable: false, defaultContent: '' },
                { data: 'type', name: 'type' },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'updated', name: 'updated_at' },
            ],
        });
    </script>
@endpush
