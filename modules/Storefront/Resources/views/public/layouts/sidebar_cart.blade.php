<aside
    x-data="SidebarCart"
    class="sidebar-cart-wrap"
    :class="{ active: $store.layout.isOpenSidebarCart }"
>
    <div class="sidebar-cart-top">
        <div class="title">
            
            {{ trans('storefront::layouts.my_cart') }}

            <div class="count skeleton" :class="{ skeleton: $store.cart.fetching }" x-text="$store.cart.quantity"></div>
        </div>

        <div class="sidebar-cart-close" @click="$store.layout.closeSidebarCart()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M15.8338 4.16663L4.16705 15.8333M4.16705 4.16663L15.8338 15.8333" stroke="#0E1E3E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg> 
        </div>
    </div>
        
    <div
        class="sidebar-cart-middle"
        :class="cartIsEmpty ? 'empty' : 'custom-scrollbar'"
    >
        <template x-if="!cartIsEmpty">
            <div class="sidebar-cart-items-wrap">
                @include('storefront::public.layouts.sidebar_cart.sidebar_cart_items')
            </div>
        </template>

        <template x-if="cartIsEmpty">
            <div class="empty-message">
                @include('storefront::public.layouts.sidebar_cart.empty_logo')

                <h4>{{ trans('storefront::cart.your_cart_is_empty') }}</h4>
            </div>
        </template>
    </div>

    <template x-if="!cartIsEmpty">
        <div class="sidebar-cart-bottom">
            <style>
                .free-shipping-progress-wrapper {
                    padding: 15px 15px 5px 15px;
                }
                .free-shipping-card {
                    background: #ffffff;
                    border: 2px solid #3562ff;
                    border-radius: 16px;
                    padding: 20px;
                    text-align: center;
                    margin-bottom: 20px;
                    box-shadow: 0 4px 12px rgba(53, 98, 255, 0.1);
                }
                .free-shipping-card h4 {
                    font-size: 16px;
                    font-weight: 700;
                    color: #0b3fa3;
                    margin-bottom: 4px;
                }
                .free-shipping-card .sub-header {
                    font-size: 13px;
                    color: #64748b;
                    margin-bottom: 15px;
                }
                
                /* Progress Bar with Handle */
                .progress-bar-container {
                    height: 14px;
                    background: #f1f5f9;
                    border-radius: 10px;
                    position: relative;
                    margin: 0 0 8px;
                    overflow: visible; /* To allow handle to show */
                }
                .progress-bar-fill {
                    height: 100%;
                    background: linear-gradient(90deg, #4f7eff 0%, #3562ff 100%);
                    border-radius: 10px;
                    transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                    position: relative;
                }
                .progress-bar-handle {
                    position: absolute;
                    right: -8px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 18px;
                    height: 18px;
                    background: #ffffff;
                    border: 3px solid #3562ff;
                    border-radius: 50%;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
                    z-index: 2;
                }
                
                .percentage-text {
                    font-size: 14px;
                    font-weight: 700;
                    color: #3562ff;
                    margin-bottom: 12px;
                    text-align: center;
                }
                
                .remaining-info {
                    font-size: 13px;
                    color: #1e293b;
                    margin-bottom: 15px;
                    line-height: 1.5;
                }
                .remaining-info b {
                    color: #3562ff;
                    font-weight: 700;
                }
                .remaining-info.success {
                    color: #10b981;
                    font-weight: 700;
                }
                
                .quick-links {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
                }
                .q-link {
                    padding: 10px 5px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 700;
                    text-decoration: none !important;
                    transition: transform 0.2s;
                    color: #ffffff;
                    text-align: center;
                }
                .q-link:hover {
                    transform: scale(1.03);
                }
                .q-link.blue {
                    background: #2563eb;
                }
                .q-link.orange {
                    background: #f5b00b;
                }
            </style>
            @if (setting('smart_shipping_enabled') && setting('smart_shipping_show_progress_bar'))
                <div class="free-shipping-progress-wrapper" x-data="{ 
                    threshold: FleetCart.freeShippingMinAmount,
                    get subtotal() { return $store.cart.subTotal },
                    get percentage() { return Math.min(Math.round((this.subtotal / this.threshold) * 100), 100) },
                    get remaining() { return Math.max(this.threshold - this.subtotal, 0) }
                }" x-show="threshold > 0">
                    <div class="free-shipping-card">
                        <div class="card-header">
                            <h4 x-show="percentage < 100">Ücretsiz Kargoya Yaklaştınız 🚚 ✨</h4>
                            <h4 x-show="percentage >= 100">Tebrikler! Kargo Bedava 🎉 🚚</h4>
                            <div class="sub-header">₺<span x-text="formatCurrency(threshold).replace('₺', '')"></span> üzeri alışverişlerde kargo ücretsiz</div>
                        </div>
                        
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" :style="'width: ' + percentage + '%'">
                                <div class="progress-bar-handle"></div>
                            </div>
                        </div>

                        <div class="percentage-text" x-text="'%' + percentage + ' tamamlandı'"></div>

                        <div class="remaining-info" x-show="percentage < 100">
                            Fırsatı kaçırmayın, ücretsiz kargo için <b x-text="formatCurrency(remaining)"></b> değerinde ürün ekleyin 🎁
                        </div>

                        <div class="remaining-info success" x-show="percentage >= 100">
                            Sepetinizde ücretsiz kargo fırsatı aktif! 🎊
                        </div>

                        <div class="quick-links">
                            <a :href="FleetCart.smartShippingButton1Link" class="q-link blue" x-text="FleetCart.smartShippingButton1Text"></a>
                            <a :href="FleetCart.smartShippingButton2Link" class="q-link orange" x-text="FleetCart.smartShippingButton2Text"></a>
                        </div>
                    </div>
                </div>
            @endif

            <h5 class="sidebar-cart-subtotal">
                {{ trans('storefront::layouts.subtotal') }}

                <span x-text="formatCurrency($store.cart.subTotal)"></span>
            </h5>

            <div class="sidebar-cart-actions">
                @if (! request()->routeIs('cart.index'))
                    <a href="{{ route('cart.index') }}" class="btn btn-default btn-view-cart">
                        {{ trans('storefront::layouts.view_cart') }}
                    </a>
                @endif

                <a href="{{ route('checkout.create') }}" class="btn btn-primary btn-checkout">
                    {{ trans('storefront::layouts.checkout') }}
                </a>
            </div>
        </div>
    </template>
</aside>
