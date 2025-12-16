@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('size_chart::size_charts.size_chart')]))
    @slot('subtitle', $sizeChart->title)

    <li><a href="{{ route('admin.size_charts.index') }}">{{ trans('size_chart::size_charts.size_charts') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('size_chart::size_charts.size_chart')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.size_charts.update', $sizeChart) }}" class="form-horizontal" id="size-chart-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        {!! $tabs->render(compact('sizeChart')) !!}
    </form>
@endsection

@include('size_chart::admin.size_charts.partials.shortcuts')

@push('globals')
    @vite([
        'modules/SizeChart/Resources/assets/admin/js/main.js',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush
