@extends('admin::layout')

@section('title', trans('admin::resource.edit', ['resource' => trans('cart::upsell.admin_title')]))

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('cart::upsell.admin_title')]))
    @slot('subtitle', trans('cart::upsell.admin_edit_subtitle', ['id' => $rule->id]))

    <li><a href="{{ route('admin.cart_upsell_rules.index') }}">{{ trans('cart::upsell.admin_title') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('cart::upsell.admin_title')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.cart_upsell_rules.update', $rule) }}" class="form-horizontal" id="upsell-rule-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        @include('cart::admin.upsell_rules.form')
    </form>
@endsection
