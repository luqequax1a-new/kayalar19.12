@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('question::questions.questions'))

    <li class="active">{{ trans('question::questions.questions') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('resource', 'questions')
    @slot('name', trans('question::questions.question'))

    @slot('thead')
        <tr>
            @include('admin::partials.table.select_all')

            <th>{{ trans('admin::admin.table.id') }}</th>
            <th>{{ trans('question::questions.table.product') }}</th>
            <th>{{ trans('question::questions.table.customer') }}</th>
            <th>{{ trans('question::questions.table.status') }}</th>
            <th>{{ trans('question::questions.table.answered') }}</th>
            <th data-sort>{{ trans('admin::admin.table.date') }}</th>
        </tr>
    @endslot
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#questions-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'product', orderable: false, searchable: false, defaultContent: '' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'status', name: 'is_approved', searchable: false },
                { data: 'answered', name: 'answer', searchable: false },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush
