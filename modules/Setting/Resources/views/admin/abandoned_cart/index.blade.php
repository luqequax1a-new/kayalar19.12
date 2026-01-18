@extends('admin::layout')

@section('title', 'Terk Edilmiş Sepet Ayarları')

@section('content_header')
    <h3>Terk Edilmiş Sepet Ayarları</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li>{{ trans('admin::sidebar.automations') }}</li>
        <li>{{ trans('admin::sidebar.email_automations') }}</li>
        <li class="active">{{ trans('admin::sidebar.settings') }}</li>
    </ol>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger fade in alert-dismissible clearfix">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <span class="alert-text">{{ trans('core::messages.the_given_data_was_invalid') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="form-horizontal" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}
        <input type="hidden" name="context" value="abandoned_cart">

        @php($settings = setting())

        <div class="row">
            <div class="col-md-12">
                @include('setting::admin.settings.tabs.abandoned_cart')

                <div class="form-group">
                    <div class="col-md-12 col-md-offset-0">
                        <button type="submit" class="btn btn-primary">{{ trans('admin::admin.buttons.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
