@extends('admin::layout')

@section('title', trans('storefront::storefront.storefront'))

@section('content_header')
    <h3>{{ trans('storefront::storefront.storefront') }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li class="active">{{ trans('storefront::storefront.storefront') }}</li>
    </ol>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.storefront.settings.update') }}" class="form-horizontal" id="storefront-settings-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        <input
            type="hidden"
            name="storefront_home_page_sections_order"
            value="{{ is_array(setting('storefront_home_page_sections_order')) ? json_encode(setting('storefront_home_page_sections_order')) : setting('storefront_home_page_sections_order') }}"
        >

        <input
            type="hidden"
            name="storefront_product_page_sections_order"
            value="{{ is_array(setting('storefront_product_page_sections_order')) ? json_encode(setting('storefront_product_page_sections_order')) : setting('storefront_product_page_sections_order') }}"
        >

        {!! $tabs->render(compact('settings')) !!}
    </form>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/admin/sass/main.scss',
        'modules/Storefront/Resources/assets/admin/js/main.js',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js'
    ])
@endpush
