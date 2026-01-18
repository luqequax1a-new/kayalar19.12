@extends('report::admin.reports.layout')

@section('filters')
    @include('report::admin.reports.filters.from')
    @include('report::admin.reports.filters.to')
    @include('report::admin.reports.filters.status')
    @include('report::admin.reports.filters.group')

    <div class="form-group">
        <label for="payment-method">{{ trans('report::admin.filters.payment_method') }}</label>

        <select name="payment_method" id="payment-method" class="custom-select-black">
            <option value="">{{ trans('report::admin.filters.please_select') }}</option>

            @foreach ($paymentMethods as $name => $paymentMethod)
                <option value="{{ $name }}" {{ $request->payment_method === $name ? 'selected' : '' }}>
                    {{ $paymentMethod->label }}
                </option>
            @endforeach
        </select>
    </div>
@endsection

@section('report_result')
    <div class="box-header">
        <h5>
            {{ trans('report::admin.filters.report_types.payment_report') }}
        </h5>
    </div>

    <div class="box-body">
        <div class="table-responsive anchor-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('report::admin.table.date') }}</th>
                        <th>{{ trans('report::admin.table.payment_method') }}</th>
                        <th>{{ trans('report::admin.table.orders') }}</th>
                        <th>{{ trans('report::admin.table.total') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($report as $data)
                        <tr>
                            <td>{{ $data->start_date->translatedFormat('j F Y') }} - {{ $data->end_date->translatedFormat('j F Y') }}</td>
                            <td>{{ $data->payment_method }}</td>
                            <td>{{ $data->total_orders }}</td>
                            <td>{{ $data->total->format() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="8">{{ trans('report::admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pull-right">
                {!! $report->links() !!}
            </div>
        </div>
    </div>
@endsection
