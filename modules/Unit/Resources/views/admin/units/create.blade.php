@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('unit::units.unit')]))

    <li><a href="{{ route('admin.units.index') }}">{{ trans('unit::units.units') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('unit::units.unit')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.units.store') }}" class="form-horizontal" id="unit-create-form" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render(compact('unit')) !!}
    </form>
@endsection
