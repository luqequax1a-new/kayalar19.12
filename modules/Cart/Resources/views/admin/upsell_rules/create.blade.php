@extends('admin::layout')

@section('title', trans('admin::resource.create', ['resource' => trans('cart::upsell.admin_title')]))

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('cart::upsell.admin_title')]))

    <li><a href="{{ route('admin.cart_upsell_rules.index') }}">{{ trans('cart::upsell.admin_title') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('cart::upsell.admin_title')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.cart_upsell_rules.store') }}" class="form-horizontal" id="upsell-rule-create-form" novalidate>
        {{ csrf_field() }}

        @include('cart::admin.upsell_rules.form')
    </form>
@endsection
