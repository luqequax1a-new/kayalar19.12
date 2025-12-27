@extends('storefront::public.layout')

@section('title', trans('storefront::checkout.checkout'))

@section('content')
    <section
        x-data="
            Checkout({
                customerEmail: '{{ auth()->user()->email ?? null }}',
                customerPhone: '{{ auth()->user()->phone ?? '' }}',
                addresses: {{ $addresses }},
                defaultAddress: {{ $defaultAddress }},
                gateways: {{ $gateways }},
                countries: {{ json_encode($countries) }},
                selectedPaymentMethod: '{{ session('checkout.payment_method') ?? '' }}',
                selectedShippingMethod: '{{ session('checkout.shipping_method') ?? '' }}'
            })
        "
        class="checkout-wrap"
    >
        <div class="container">
            @include('storefront::public.cart.index.steps')

            <form class="checkout-form" @input="errors.clear($event.target.name)">
                <div class="checkout-inner">
                    <div class="checkout-left">
                        @include('storefront::public.checkout.create.form.account_details')
                        @include('storefront::public.checkout.create.form.shipping_details')
                        @include('storefront::public.checkout.create.form.order_note')

                        <div class="row">
                            <div class="col-md-18">
                                <div class="form-group ship-to-different-address-label">
                                    <div class="ship-toggle-card d-flex align-items-center justify-content-between">
                                        <label for="ship-to-different-address" class="form-check-label">
                                            {{ trans('storefront::checkout.ship_to_different_address') }}
                                        </label>

                                        <label class="toggle-switch">
                                            <input
                                                type="checkbox"
                                                name="has_different_billing"
                                                id="ship-to-different-address"
                                                x-model="form.ship_to_a_different_address"
                                            >
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @include('storefront::public.checkout.create.form.billing_details')
                    </div>

                    <div class="checkout-right">
                        @include('storefront::public.checkout.create.payment')
                        @include('storefront::public.checkout.create.shipping')
                    </div>
                </div>

                @include('storefront::public.checkout.create.order_summary')
            </form>

            @if (setting('authorizenet_enabled'))
                <template x-if="authorizeNetToken">
                    <form
                        x-ref="authorizeNetForm"
                        method="post"
                        action="{{
                            setting('authorizenet_test_mode') ?
                            'https://test.authorize.net/payment/payment' :
                            'https://accept.authorize.net/payment/payment'
                        }}"
                    >
                        <input type="hidden" name="token" :value="authorizeNetToken" />

                        <button type="submit"></button>
                    </form>
                </template>
            @endif

            @if (setting('payfast_enabled'))
                <form
                    x-ref="payFastForm"
                    method="post"
                    action="https://{{ setting('payfast_test_mode') ? 'sandbox.' : '' }}payfast.co.za/eng/process"
                >
                    <template x-for="(value, name, index) in payFastFormFields" :key="index">
                        <input :name="name" type="hidden" :value="value" />
                    </template>
                </form>
            @endif
        </div>

        
    </section>
@endsection

@push('pre-scripts')
    @if (setting('stripe_enabled') && setting('stripe_integration_type') === 'embedded_form')
        <script defer src="https://js.stripe.com/v3/"></script>
    @endif

    @if (setting('paypal_enabled'))
        <script src="https://www.paypal.com/sdk/js?client-id={{ setting('paypal_client_id') }}&currency={{ setting('default_currency') }}&disable-funding=credit,card,venmo,sepa,bancontact,eps,giropay,ideal,mybank,p24,p24"></script>
    @endif

    @if (setting('paytm_enabled'))
        <script async src="https://securegw{{ setting('paytm_test_mode') ? '-stage' : '' }}.paytm.in/merchantpgpui/checkoutjs/merchants/{{ setting('paytm_merchant_id') }}.js"></script>
    @endif

    @if (setting('razorpay_enabled'))
        <script async src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endif

    @if (setting('mercadopago_enabled'))
        <script async src="https://sdk.mercadopago.com/js/v2"></script>
    @endif

    @if (setting('flutterwave_enabled'))
        <script async src="https://checkout.flutterwave.com/v3.js"></script>
    @endif

    @if (setting('paystack_enabled'))
        <script async src="https://js.paystack.co/v1/inline.js"></script>
    @endif

    @if (setting('payfast_enabled'))
        <script async src="https://www.payfast.co.za/onsite/engine.js"></script>
    @endif
@endpush

@push('globals')
    <script>
        FleetCart.stripePublishableKey = '{{ setting("stripe_publishable_key") }}',
        FleetCart.stripeEnabled = {{ setting("stripe_enabled") ? 'true' : 'false' }},
        FleetCart.stripeIntegrationType = '{{ setting("stripe_integration_type") }}',
        FleetCart.langs['storefront::checkout.payment_for_order'] = '{{ trans("storefront::checkout.payment_for_order") }}';
        FleetCart.langs['storefront::checkout.remember_about_your_order'] = '{{ trans("storefront::checkout.remember_about_your_order") }}';
        window.codFeeDisplayMode = '{{ setting('cod_fee_display_mode') ?: 'separate_line' }}';
    </script>

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/checkout/create/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/checkout/create/main.js',
    ])
@endpush

@push('globals')
    <script>
        window.checkoutDebugLog = window.checkoutDebugLog || [];
        function logCheckout(type, payload) {
            payload = payload || {};
            var entry = {
                t: new Date().toISOString(),
                type: type,
                url: window.location.pathname + window.location.search
            };
            for (var k in payload) { if (Object.prototype.hasOwnProperty.call(payload, k)) { entry[k] = payload[k]; } }
            window.checkoutDebugLog.push(entry);
            try { console.log('[CHECKOUT]', entry); } catch (e) {}
        }
        logCheckout('page_load', {
            user_id: "{{ auth()->id() ?? null }}",
            cart_id: "{{ $cart->id ?? null }}"
        });
        window.addEventListener('error', function (e) {
            logCheckout('js_error', {
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                colno: e.colno
            });
        });
        window.addEventListener('unhandledrejection', function (e) {
            var r = e.reason;
            logCheckout('js_unhandled_promise', {
                reason: r ? (r.message || String(r)) : null
            });
        });
        function redirectTo(url, reason) { logCheckout('redirect', { to: url, reason: reason || null }); window.location.href = url; }
        (function(){
            function onChange(selector, handler){
                document.addEventListener('change', function(e){
                    var t = e.target; if (!t) return; if (t.matches(selector)) { handler(t); }
                });
            }
            if (window.$ && $.ajaxSetup) {
                var lastAjaxId = 0;
                $.ajaxSetup({
                    beforeSend: function (xhr, settings) {
                        this.__ajaxId = ++lastAjaxId;
                        this.__startedAt = Date.now();
                        logCheckout('ajax_start', { id: this.__ajaxId, method: settings.type, url: settings.url, data: settings.data });
                    },
                    complete: function (xhr, status) {
                        var dur = this.__startedAt ? (Date.now() - this.__startedAt) : undefined;
                        logCheckout('ajax_end', { id: this.__ajaxId, status: status, http_status: xhr.status, url: this.url, duration_ms: dur, response_sample: (xhr.responseText || '').slice(0, 300) });
                    },
                    error: function (xhr, status, error) {
                        logCheckout('ajax_error', { id: this.__ajaxId, status: status, http_status: xhr.status, error: error, url: this.url });
                    }
                });
            }
            if (window.fetch) {
                var _origFetch = window.fetch;
                window.fetch = function(){
                    var args = arguments;
                    var url = args[0];
                    var options = args[1] || {};
                    var startedAt = Date.now();
                    logCheckout('fetch_start', { url: url, method: options.method || 'GET' });
                    return _origFetch.apply(this, args).then(function(res){
                        logCheckout('fetch_end', { url: url, status: res.status, ms: Date.now() - startedAt });
                        return res;
                    }).catch(function(err){
                        logCheckout('fetch_error', { url: url, error: (err && (err.message || String(err))) || 'unknown', ms: Date.now() - startedAt });
                        throw err;
                    });
                };
            }
            if (window.axios && axios.interceptors) {
                var lastAxiosId = 0;
                axios.interceptors.request.use(function(config){
                    config.__ajaxId = ++lastAxiosId;
                    config.__startedAt = Date.now();
                    logCheckout('axios_start', { id: config.__ajaxId, method: (config.method || 'GET').toUpperCase(), url: config.url, data: config.data });
                    return config;
                }, function(error){
                    var cfg = error.config || {};
                    logCheckout('axios_request_error', { id: cfg.__ajaxId, http_status: (error.response && error.response.status) || 0, error: String(error.message || error), url: cfg.url });
                    return Promise.reject(error);
                });
                axios.interceptors.response.use(function(response){
                    var cfg = response.config || {};
                    var dur = cfg.__startedAt ? (Date.now() - cfg.__startedAt) : undefined;
                    var sample;
                    try { sample = typeof response.data === 'string' ? response.data : JSON.stringify(response.data); } catch (_e) { sample = ''; }
                    logCheckout('axios_end', { id: cfg.__ajaxId, http_status: response.status, url: cfg.url, ms: dur, response_sample: (sample || '').slice(0,300) });
                    try {
                        var redirectUrl = response.data && response.data.redirectUrl;
                        if (redirectUrl) { logCheckout('redirect', { to: redirectUrl, reason: 'purchase_response' }); }
                        else if (cfg.url && /\/checkout\/\d+\/complete/.test(String(cfg.url))) { logCheckout('redirect', { to: '/checkout/complete', reason: 'order_placed' }); }
                    } catch(_e) {}
                    return response;
                }, function(error){
                    var cfg = error.config || {};
                    var msg = (error.response && error.response.data && error.response.data.message) || error.message || '';
                    logCheckout('axios_response_error', { id: cfg.__ajaxId, http_status: (error.response && error.response.status) || 0, error: String(msg), url: cfg.url, ms: cfg.__startedAt ? (Date.now() - cfg.__startedAt) : undefined });
                    return Promise.reject(error);
                });
            }
            onChange('input[name="shipping_method"]', function(el){ logCheckout('shipping_change', { shipping_method: el.value }); });
            onChange('input[name="payment_method"]', function(el){ logCheckout('payment_change', { payment_method: el.value }); });
            document.addEventListener('click', function(e){
                var btn = e.target && e.target.closest ? e.target.closest('#place-order-button') : null;
                if (!btn) return;
                var text = (btn.innerText || btn.textContent || '').trim();
                logCheckout('place_order_click', { disabled: !!btn.disabled, text: text });
            });
        })();
    </script>
@endpush
