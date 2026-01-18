<header x-ref="header" x-data="Header" class="header-wrap">
    <div
        class="header-wrap-inner"
        :class="{
            sticky: isStickyHeader,
            show: isShowingStickyHeader
        }"
    >
        @include('storefront::public.layouts.announcement_bar')
        <div class="container">
            <div class="d-flex flex-nowrap justify-content-between position-relative">
                <div class="header-column-left align-items-center">
                    <div class="sidebar-menu-icon-wrap" @click="$store.layout.openSidebarMenu()">
                        <div class="sidebar-menu-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="150px" height="150px">
                                <path d="M 3 9 A 1.0001 1.0001 0 1 0 3 11 L 47 11 A 1.0001 1.0001 0 1 0 47 9 L 3 9 z M 3 24 A 1.0001 1.0001 0 1 0 3 26 L 47 26 A 1.0001 1.0001 0 1 0 47 24 L 3 24 z M 3 39 A 1.0001 1.0001 0 1 0 3 41 L 47 41 A 1.0001 1.0001 0 1 0 47 39 L 3 39 z"></path>
                            </svg>
                        </div>
                    </div>

                    <a href="{{ route('home') }}" class="header-logo">
                        @if (is_null($logo))
                            <h3>{{ setting('store_name') }}</h3>
                        @else
                            @php($isAbsoluteLogo = is_string($logo) && (str_starts_with($logo, 'http') || str_starts_with($logo, '//')))
                            @php($logoSrc = $isAbsoluteLogo ? ((parse_url($logo, PHP_URL_PATH) ?: $logo) . (parse_url($logo, PHP_URL_QUERY) ? '?' . parse_url($logo, PHP_URL_QUERY) : '')) : $logo)
                            <img src="{{ $logoSrc }}" alt="{{ setting('store_name') ?? 'Logo' }}">
                        @endif
                    </a>
                </div>

                @include('storefront::public.layouts.header.header_search')

                <div class="header-column-right d-flex">
                    <div class="header-column-right-item header-localization">
                        <div class="icon-wrap" @click="$store.layout.openLocalizationMenu()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                <line x1="9" y1="10" x2="15" y2="10"></line>
                                <line x1="9" y1="14" x2="13" y2="14"></line>
                            </svg>
                        </div>
                    </div>

                    <a href="{{ route('compare.index') }}" class="header-column-right-item header-compare">
                        <div class="icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M3.58008 5.15991H17.4201C19.0801 5.15991 20.4201 6.49991 20.4201 8.15991V11.4799" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6.74008 2L3.58008 5.15997L6.74008 8.32001" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20.4201 18.84H6.58008C4.92008 18.84 3.58008 17.5 3.58008 15.84V12.52" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M17.26 21.9999L20.42 18.84L17.26 15.6799" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>                      
                            
                            <div class="count" x-text="$store.compare.count">{{ $compareCount }}</div>
                        </div>
                    </a>

                    <a href="{{ route('account.wishlist.index') }}" class="header-column-right-item header-wishlist">
                        <div class="icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12.62 20.81C12.28 20.93 11.72 20.93 11.38 20.81C8.48 19.82 2 15.69 2 8.68998C2 5.59998 4.49 3.09998 7.56 3.09998C9.38 3.09998 10.99 3.97998 12 5.33998C13.01 3.97998 14.63 3.09998 16.44 3.09998C19.51 3.09998 22 5.59998 22 8.68998C22 15.69 15.52 19.82 12.62 20.81Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>                      
                            
                            <div class="count" x-text="$store.wishlist.count">{{ $wishlistCount }}</div>
                        </div>
                    </a>
                    
                    <a
                        href="{{ route('cart.index') }}"
                        class="header-column-right-item header-cart"
                        @click="$store.layout.openSidebarCart($event)"
                    >  
                        <div class="icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M7.5 7.67001V6.70001C7.5 4.45001 9.31 2.24001 11.56 2.03001C14.24 1.77001 16.5 3.88001 16.5 6.51001V7.89001" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.00001 22H15C19.02 22 19.74 20.39 19.95 18.43L20.7 12.43C20.97 9.99 20.27 8 16 8H8.00001C3.73001 8 3.03001 9.99 3.30001 12.43L4.05001 18.43C4.26001 20.39 4.98001 22 9.00001 22Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                            <div class="count" x-text="$store.cart.quantity">{{ $cartQuantity }}</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
