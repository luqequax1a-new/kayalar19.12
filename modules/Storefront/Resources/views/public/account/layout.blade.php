@extends('storefront::public.layout')

@section('breadcrumb')
    @if (request()->routeIs('account.dashboard.index'))
        <li class="active">{{ trans('storefront::account.pages.my_account') }}</li>
    @else
        <li><a href="{{ route('account.dashboard.index') }}">{{ trans('storefront::account.pages.my_account') }}</a></li>
    @endif

    @yield('account_breadcrumb')
@endsection

@section('content')
    <section class="account-wrap">
        <div class="container">
            <div class="account-wrap-inner">
                <div class="account-left">
                    <ul class="account-sidebar list-inline d-flex flex-column">
                        <li class="{{ request()->routeIs('account.dashboard.index') ? 'active' : '' }}">
                            <a href="{{ route('account.dashboard.index') }}">
                                <i class="las la-tachometer-alt"></i>

                                {{ trans('storefront::account.pages.dashboard') }}
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('account.orders.index') ? 'active' : '' }}">
                            <a href="{{ route('account.orders.index') }}">
                                <i class="las la-cart-arrow-down"></i>

                                {{ trans('storefront::account.pages.my_orders') }}
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('account.downloads.index') ? 'active' : '' }}">
                            <a href="{{ route('account.downloads.index') }}">
                                <i class="las la-download"></i>

                                {{ trans('storefront::account.pages.my_downloads') }}
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('account.wishlist.index') ? 'active' : '' }}">
                            <a href="{{ route('account.wishlist.index') }}">
                                <i class="lar la-heart"></i>

                                {{ trans('storefront::account.pages.my_wishlist') }}

                                <span class="count" x-text="$store.wishlist.count"></span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('account.tickets.index') || request()->routeIs('account.tickets.show') ? 'active' : '' }}">
                            <a href="{{ route('account.tickets.index') }}">
                                <i class="las la-comments"></i>

                                {{ trans('ticket::ticket.tickets') }}
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('account.reviews.index') ? 'active' : '' }}">
                            <a href="{{ route('account.reviews.index') }}">
                                <i class="las la-comment"></i>

                                {{ trans('storefront::account.pages.my_reviews') }}
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('account.coupons.index') ? 'active' : '' }}">
                            <a href="{{ route('account.coupons.index') }}">
                                <i class="las la-ticket-alt"></i>

                                {{ trans('storefront::account.pages.my_coupons') }}
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('account.addresses.index') ? 'active' : '' }}">
                            <a href="{{ route('account.addresses.index') }}">
                                <i class="las la-address-book"></i>

                                {{ trans('storefront::account.pages.my_addresses') }}
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('account.profile.edit') ? 'active' : '' }}">
                            <a href="{{ route('account.profile.edit') }}">
                                <i class="las la-user-circle"></i>

                                {{ trans('storefront::account.pages.my_profile') }}
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('logout') }}">
                                <i class="las la-sign-out-alt"></i>

                                {{ trans('storefront::account.pages.logout') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="account-right">
                    <div class="panel-wrap">
                        @yield('panel')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/main.scss'
    ])
@endpush

@push('scripts')
    @if(session('toast'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = @json(session('toast'));
                if (toast && toast.message) {
                    showToast(toast.message, toast.type || 'success');
                }
            });

            function showToast(message, type = 'success') {
                const colors = {
                    success: { bg: '#10b981', border: '#059669' },
                    error: { bg: '#ef4444', border: '#dc2626' },
                    info: { bg: '#3b82f6', border: '#2563eb' }
                };
                const color = colors[type] || colors.success;

                const toast = document.createElement('div');
                toast.style.cssText = `
                    position: fixed;
                    bottom: 24px;
                    right: 24px;
                    min-width: 320px;
                    max-width: 400px;
                    background: ${color.bg};
                    color: white;
                    padding: 16px 20px;
                    border-radius: 12px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    font-size: 14px;
                    font-weight: 500;
                    animation: slideIn 0.3s ease-out;
                    border-left: 4px solid ${color.border};
                `;

                toast.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>${message}</span>
                `;

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.animation = 'slideOut 0.3s ease-in forwards';
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            }
        </script>
        <style>
            @keyframes slideIn {
                from { transform: translateX(120%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(120%); opacity: 0; }
            }
        </style>
    @endif
@endpush
