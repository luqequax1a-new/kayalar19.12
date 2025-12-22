@extends('storefront::public.account.layout')

@section('title', trans('storefront::account.pages.dashboard'))

@section('panel')
    <div class="panel">
        <div class="panel-header">
            <h4>{{ trans('storefront::account.dashboard.account_information') }}</h4>

            <a href="{{ route('account.profile.edit') }}">
                {{ trans('storefront::account.dashboard.edit') }}
            </a>
        </div>

        <div class="panel-body">
            <ul class="list-inline user-info">
                <li>
                    <i class="las la-user-circle"></i>

                    <span>{{ $account->full_name }}</span>
                </li>

                <li>
                    <i class="las la-envelope"></i>
                    
                    <span>{{ $account->email }}</span>
                </li>
            </ul>
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/dashboard/main.scss', 
    ])
@endpush
