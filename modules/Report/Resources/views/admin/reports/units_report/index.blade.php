@extends('report::admin.reports.layout')

@section('filters')
    <div class="form-group">
        <label for="unit">{{ trans('report::admin.filters.unit') }}</label>
        <input type="text" name="unit" class="form-control" id="unit" value="{{ $request->unit }}">
    </div>

    <div class="form-group">
        <label for="products-count-min">{{ trans('report::admin.filters.products_count_min') }}</label>
        <input type="number" name="products_count_min" class="form-control" id="products-count-min" value="{{ $request->products_count_min }}">
    </div>

    <div class="form-group">
        <label for="products-count-max">{{ trans('report::admin.filters.products_count_max') }}</label>
        <input type="number" name="products_count_max" class="form-control" id="products-count-max" value="{{ $request->products_count_max }}">
    </div>
@endsection

@section('report_result')
    <div class="box-header">
        <h5>
            {{ trans('report::admin.filters.report_types.units_report') }}
        </h5>
    </div>

    <div class="box-body">
        <div class="table-responsive anchor-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('report::admin.table.unit') }}</th>
                        <th>{{ trans('report::admin.table.products_count') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($report as $unit)
                        <tr>
                            <td>{{ $unit->name }}</td>
                            <td>{{ $unit->products_count }}</td>
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
