<aside class="localization" :class="{ active: $store.layout.isOpenlocalizationMenu }">
    <div class="localization-header">
        <h3>{{ trans('storefront::layouts.support_center') }}</h3>

        <div class="localization-cross-icon" @click="$store.layout.closeLocalizationMenu()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M4.00073 11.9996L12 4.00037" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 11.9996L4.00073 4.00037" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </div>

    <div class="localization-content">
        <div class="support-options">
            {{-- WhatsApp Support --}}
            @if (setting('store_phone'))
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', setting('store_phone')) }}" class="support-item whatsapp" target="_blank">
                    <div class="icon-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 1 1-7.6-7.6 8.38 8.38 0 0 1 3.8.9L21 3.5Z"/>
                        </svg>
                    </div>
                    <div class="text-box">
                        <span class="title">{{ trans('storefront::layouts.whatsapp_support') }}</span>
                        <span class="subtitle">{{ trans('storefront::layouts.whatsapp_desc') }}</span>
                    </div>
                    <div class="arrow-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>

                {{-- Call Us --}}
                <a href="tel:{{ setting('store_phone') }}" class="support-item call">
                    <div class="icon-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <div class="text-box">
                        <span class="title">{{ trans('storefront::layouts.call_us') }}</span>
                        <span class="subtitle">{{ setting('store_phone') }}</span>
                    </div>
                    <div class="arrow-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </div>
                </a>
            @endif

            {{-- Order Tracking --}}
            <a href="{{ route('order_tracking.index') }}" class="support-item tracking">
                <div class="icon-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/>
                        <path d="M16.5 9.4 7.55 4.24"/>
                        <polyline points="3.29 7 12 12 20.71 7"/>
                        <line x1="12" y1="22" x2="12" y2="12"/>
                        <circle cx="18.5" cy="15.5" r="2.5"/>
                        <path d="M20.27 17.27 22 19"/>
                    </svg>
                </div>
                <div class="text-box">
                    <span class="title">{{ trans('storefront::layouts.order_tracking') }}</span>
                    <span class="subtitle">{{ trans('storefront::layouts.order_tracking_desc') }}</span>
                </div>
                <div class="arrow-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>
        </div>
        
    </div>
</aside>