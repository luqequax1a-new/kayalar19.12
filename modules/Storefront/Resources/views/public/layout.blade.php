<!DOCTYPE html>
<html lang="{{ locale() }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#000000">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">

        @stack('lcp_preload')

        <title>
            @hasSection('title')
                @yield('title')
            @else
                @if (setting('store_tagline'))
                    {{ setting('store_tagline') }} -
                @endif

                {{ setting('store_name') }}
            @endif
        </title>

        <script>
            window.FleetcartSEO = {
                baseTitle: document.title,
            };
        </script>

        @stack('meta')
        @yield('canonical')
        @PWA

        {{-- Enhanced Analytics Tracking System --}}
        @if(file_exists(resource_path('../modules/Analytics/Resources/views/tracking-scripts.blade.php')))
            @include('analytics::tracking-scripts')
        @else
            {{-- Google Tag Manager --}}
            @if(setting('google_tag_manager_id'))
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{ setting('google_tag_manager_id') }}');</script>
            @endif

            {{-- Google Analytics GA4 --}}
            @if(!setting('google_tag_manager_id') && setting('google_analytics_measurement_id'))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_measurement_id') }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{{ setting('google_analytics_measurement_id') }}');
            </script>
            @endif

            {{-- Facebook Pixel --}}
            @if(setting('facebook_pixel_id'))
            <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '{{ setting('facebook_pixel_id') }}');
                fbq('track', 'PageView');
            </script>
            <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id={{ setting('facebook_pixel_id') }}&ev=PageView&noscript=1"/></noscript>
            @endif

            {{-- TikTok Pixel --}}
            @if(setting('tiktok_pixel_id'))
            <script>
                !function (w, d, t) {
                  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
                  ttq.load('{{ setting('tiktok_pixel_id') }}');
                  ttq.page();
                }(window, document, 'ttq');
            </script>
            @endif
        @endif

        <link rel="shortcut icon" href="{{ $favicon }}" type="image/x-icon">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="{{ font_url(setting('storefront_display_font', 'Poppins')) }}" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="{{ font_url(setting('storefront_display_font', 'Poppins')) }}"></noscript>

        @include('storefront::public.partials.variables')

        <style>
            .header{min-height:60px}
            .product-card{min-height:260px}
        </style>

        @vite([
            'modules/Storefront/Resources/assets/public/sass/vendors/_bootstrap.scss',
            'modules/Storefront/Resources/assets/public/sass/vendors/_line-awesome.scss',
            'modules/Storefront/Resources/assets/public/sass/vendors/_swiper.scss',
            'modules/Storefront/Resources/assets/public/sass/vendors/_toastify.scss',
            'modules/Storefront/Resources/assets/public/sass/app.scss',
            'modules/Storefront/Resources/assets/public/js/app.js',
            'modules/Storefront/Resources/assets/public/js/main.js'
        ])

        <style id="upsell-critical-css">
[x-cloak]{display:none!important}
.fc-upsell-wrapper{position:fixed;bottom:24px;right:24px;z-index:2147483640;pointer-events:none;max-width:440px;width:calc(100vw - 48px)}
@keyframes slideInRight{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:translateX(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.fc-upsell-toast-minimal{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,.08);overflow:hidden;pointer-events:auto;position:relative;border:none;max-width:460px;animation:slideInRight .4s cubic-bezier(.16,1,.3,1)}
.fc-upsell-minimal-header{background:linear-gradient(135deg,#ff7a6b 0%,#ff9a6b 100%);padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.fc-upsell-minimal-title{color:#1a1a1a;font-size:1rem;font-weight:700;line-height:1.2;flex:1;text-align:center}
.fc-upsell-minimal-close{background:transparent;border:none;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;color:#1a1a1a}
.fc-upsell-minimal-close:hover{background:rgba(0,0,0,.05)}
.fc-upsell-minimal-close svg{width:16px;height:16px}
.fc-upsell-minimal-countdown{background:#fff1f2;color:#e11d48;font-size:.8125rem;font-weight:600;padding:10px 20px;text-align:center;display:flex;align-items:center;justify-content:center;gap:6px}
.fc-upsell-minimal-content{padding:20px}
.fc-upsell-minimal-product{display:flex;gap:16px;align-items:center}
.fc-upsell-minimal-image{width:90px;height:90px;border-radius:12px;overflow:hidden;flex-shrink:0;background:#f8fafc}
.fc-upsell-minimal-image img{width:100%;height:100%;object-fit:cover}
.fc-upsell-minimal-info{flex:1;min-width:0}
.fc-upsell-minimal-subtitle{color:#94a3b8;font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px}
.fc-upsell-minimal-name{color:#1a1a1a;font-size:.9375rem;font-weight:700;line-height:1.3;margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.fc-upsell-minimal-prices{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.fc-upsell-minimal-price-old{color:#94a3b8;font-size:.875rem;text-decoration:line-through;font-weight:500}
.fc-upsell-minimal-price-new{color:#10b981;font-size:1.375rem;font-weight:800}
.fc-upsell-minimal-discount-badge{background:linear-gradient(135deg,#ec4899 0%,#f472b6 100%);color:#fff;font-size:.75rem;font-weight:700;padding:4px 10px;border-radius:20px}
.fc-upsell-minimal-actions{padding:0 20px 20px;display:grid;grid-template-columns:1fr 2fr;gap:12px}
.fc-upsell-minimal-btn{padding:14px 20px;border-radius:12px;font-size:.9375rem;font-weight:700;border:none;cursor:pointer;transition:all .2s;text-align:center}
.fc-upsell-minimal-btn.secondary{background:#f1f5f9;color:#64748b}
.fc-upsell-minimal-btn.secondary:hover{background:#e2e8f0}
.fc-upsell-minimal-btn.primary{background:#1e293b;color:#fff}
.fc-upsell-minimal-btn.primary:hover:not(:disabled){background:#0f172a;transform:translateY(-1px)}
.fc-upsell-minimal-btn:disabled{opacity:.6;cursor:not-allowed}
.fc-upsell-modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,.5);backdrop-filter:blur(10px);z-index:2147483645;display:flex;align-items:center;justify-content:center;padding:24px;pointer-events:auto;animation:fadeIn .3s ease}
.fc-upsell-modal-minimal{background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.15);max-width:420px;width:90%;max-height:85vh;overflow-y:auto;position:relative;animation:modalScaleUp .4s cubic-bezier(.34,1.56,.64,1)}
@keyframes modalScaleUp{from{opacity:0;transform:scale(.85) translateY(30px)}to{opacity:1;transform:scale(1) translateY(0)}}
.fc-upsell-modal-minimal-header{background:linear-gradient(135deg,#ff7a6b 0%,#ff9a6b 100%);padding:16px 24px;display:flex;align-items:center;justify-content:space-between}
.fc-upsell-modal-minimal-title{color:#1a1a1a;font-size:1.125rem;font-weight:700;flex:1}
.fc-upsell-modal-minimal-close{background:transparent;border:none;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#1a1a1a}
.fc-upsell-modal-minimal-close:hover{background:rgba(0,0,0,.05)}
.fc-upsell-modal-minimal-close svg{width:16px;height:16px}
.fc-upsell-modal-minimal-product{padding:16px 24px 20px;text-align:center}
.fc-upsell-modal-minimal-product-name{font-size:1.125rem;font-weight:600;color:#1a1a1a;margin-bottom:8px}
.fc-upsell-modal-minimal-product-price{font-size:1.5rem;font-weight:800;color:#10b981}
.fc-upsell-modal-minimal-content{padding:0 24px 24px}
.fc-upsell-modal-minimal-label{font-size:.875rem;font-weight:600;color:#666;margin-bottom:12px;text-transform:uppercase}
.fc-upsell-modal-minimal-variants{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;margin-bottom:24px}
.fc-upsell-modal-minimal-variant{display:flex;flex-direction:column;align-items:center;padding:12px;border:2px solid #e5e7eb;border-radius:12px;background:#fff;cursor:pointer;transition:all .2s;text-align:center}
.fc-upsell-modal-minimal-variant:hover{border-color:#d1d5db;background:#f9fafb}
.fc-upsell-modal-minimal-variant.active{border-color:#2563eb;background:#eff6ff;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.fc-upsell-modal-minimal-variant-image{width:60px;height:60px;object-fit:cover;border-radius:8px;margin-bottom:8px}
.fc-upsell-modal-minimal-variant span{font-size:.875rem;font-weight:500;color:#374151}
.fc-upsell-modal-minimal-quantity{margin-bottom:24px}
.fc-upsell-modal-minimal-quantity-controls{display:flex;align-items:center;gap:8px;margin-bottom:16px}
.fc-upsell-modal-minimal-qty-btn{width:40px;height:40px;border:2px solid #e5e7eb;background:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.25rem;font-weight:600;color:#374151;transition:all .2s}
.fc-upsell-modal-minimal-qty-btn:hover:not(:disabled){border-color:#2563eb;background:#eff6ff;color:#2563eb}
.fc-upsell-modal-minimal-qty-btn:disabled{opacity:.4;cursor:not-allowed}
.fc-upsell-modal-minimal-qty-input{flex:1;display:flex;align-items:center;background:#f9fafb;border:2px solid #e5e7eb;border-radius:10px;overflow:hidden}
.fc-upsell-modal-minimal-input{flex:1;border:none;background:transparent;padding:10px 12px;font-size:1rem;font-weight:600;text-align:center;color:#1a1a1a;outline:none}
.fc-upsell-modal-minimal-summary{background:#f9fafb;border-radius:12px;padding:16px;margin-bottom:24px}
.fc-upsell-modal-minimal-summary-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.fc-upsell-modal-minimal-summary-row:last-child{margin-bottom:0}
.fc-upsell-modal-minimal-summary-row.total{padding-top:8px;border-top:2px solid #e5e7eb;font-weight:700}
.fc-upsell-modal-minimal-summary-row span:first-child{color:#666;font-size:.875rem}
.fc-upsell-modal-minimal-summary-row span:last-child{color:#1a1a1a;font-weight:600}
.fc-upsell-modal-minimal-summary-row.total span:last-child{color:#2563eb;font-size:1.125rem}
.fc-upsell-modal-minimal-actions{display:flex;gap:12px}
.fc-upsell-modal-minimal-btn{flex:1;padding:14px 20px;border:none;border-radius:12px;font-size:.9375rem;font-weight:600;cursor:pointer;transition:all .2s;text-align:center}
.fc-upsell-modal-minimal-btn.secondary{background:#f3f4f6;color:#374151}
.fc-upsell-modal-minimal-btn.secondary:hover{background:#e5e7eb}
.fc-upsell-modal-minimal-btn.primary{background:#2563eb;color:#fff}
.fc-upsell-modal-minimal-btn.primary:hover:not(:disabled){background:#1d4ed8;transform:translateY(-1px);box-shadow:0 4px 12px rgba(37,99,235,.3)}
.fc-upsell-modal-minimal-btn:disabled{opacity:.5;cursor:not-allowed}
@media (max-width:640px){
.fc-upsell-wrapper{bottom:12px;left:12px;right:12px;width:auto;max-width:none}
.fc-upsell-modal-minimal{width:95%;max-height:90vh;border-radius:16px}
}
/* Multi-offer styles - Compact */
.fc-upsell-offer-card{background:#fff;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,.12);overflow:hidden;pointer-events:auto;position:relative;border:1px solid rgba(0,0,0,.05);margin-top:10px}
.upsell-enter{animation:upsellPopIn .4s cubic-bezier(.34,1.56,.64,1)}
@keyframes upsellPopIn{from{opacity:0;transform:scale(.95) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.fc-upsell-header{background:linear-gradient(135deg,#ff6b6b 0%,#ff8e53 100%);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;color:#fff}
.fc-upsell-header-title{font-size:1rem;font-weight:700;margin:0;flex:1;text-align:center}
.fc-upsell-close-btn{background:rgba(255,255,255,.2);border:none;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;color:#fff}
.fc-upsell-close-btn:hover{background:rgba(255,255,255,.35)}
.fc-upsell-body{padding:16px;position:relative}
.fc-upsell-timer{background:#fff5f5;color:#ef4444;font-size:.8125rem;font-weight:600;padding:8px 12px;border-radius:8px;text-align:center;margin-bottom:12px;display:flex;align-items:center;justify-content:center;gap:6px;border:1px solid #fee2e2}
.fc-upsell-item{display:flex;flex-direction:row;gap:12px;margin-bottom:16px;align-items:center}
.fc-upsell-item-img{width:100px;height:100px;border-radius:12px;overflow:hidden;flex-shrink:0;background:#f8fafc}
.fc-upsell-item-img img{width:100%;height:100%;object-fit:cover;background:#fff}
.fc-upsell-item-content{display:flex;flex-direction:column;gap:4px;text-align:left;flex:1;min-width:0}
.fc-upsell-item-subtitle{color:#ff6b6b;font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em}
.fc-upsell-item-name{font-size:.9375rem;font-weight:700;color:#0f172a;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.fc-upsell-item-price-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.fc-upsell-price-new{font-size:1.25rem;font-weight:800;color:#10b981}
.fc-upsell-price-old{font-size:.875rem;color:#94a3b8;text-decoration:line-through;font-weight:500}
.fc-upsell-badge{background:#ff6b6b;color:#fff;padding:2px 6px;border-radius:4px;font-size:.6875rem;font-weight:700}
.fc-upsell-btn-row{display:grid;grid-template-columns:auto 1fr;gap:10px}
.fc-upsell-btn{border-radius:12px;height:44px;font-size:.9375rem;font-weight:700;cursor:pointer;transition:all .2s;border:none;display:flex;align-items:center;justify-content:center;padding:0 16px}
.fc-btn-reject{background:#f1f5f9;color:#64748b}
.fc-btn-add{background:#0f172a;color:#fff}
.fc-btn-add:hover{background:#1e293b}
.fc-upsell-modal{background:#fff;border-radius:32px;padding:32px;width:100%;max-width:500px;box-shadow:0 25px 60px rgba(0,0,0,.2);animation:modalScaleUp .4s cubic-bezier(.34,1.56,.64,1)}
.fc-upsell-variant-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;max-height:500px;overflow-y:auto;padding:8px;justify-items:center}
.fc-variant-box{border:none;border-radius:0;padding:0;cursor:pointer;transition:all .2s;text-align:center;background:transparent;width:100%;max-width:140px}
.fc-variant-box img{width:110px;height:110px;object-fit:cover;border-radius:16px;margin:0 auto 10px;display:block;box-shadow:0 2px 8px rgba(0,0,0,.1);transition:all .2s}
.fc-variant-box.active img{box-shadow:0 4px 16px rgba(255,107,107,.3);border:3px solid #ff6b6b}
.fc-variant-box span{font-size:.875rem;font-weight:600;color:#1e293b;display:block;line-height:1.3}
.fc-upsell-decimal-quantity-card{flex-direction:column;align-items:flex-start;background:#fff;border:1px solid #ededed;border-radius:12px;padding:20px;display:flex;gap:10px;width:100%;box-shadow:0 2px 8px rgba(17,24,39,.04);margin-bottom:20px}
.fc-upsell-decimal-quantity-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;width:100%}
.fc-upsell-decimal-quantity-title{font-size:15px;font-weight:600;margin-bottom:4px;color:#0f172a}
.fc-upsell-decimal-quantity-desc{font-size:13px;color:#64748b;line-height:1.4}
.fc-upsell-decimal-quantity-main{display:grid;grid-template-columns:44px max-content 44px;align-items:center;gap:0;width:max-content;margin:10px auto 4px;border:1px solid #e6e6e6;border-radius:999px;background:#fff}
.fc-upsell-btn-quantity{height:44px;width:44px;border-radius:999px;border:none;background:transparent;line-height:1;font-size:20px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;margin:0;cursor:pointer;color:#0f172a}
.fc-upsell-btn-quantity:hover{color:#ff6b6b}
.fc-upsell-btn-quantity[disabled]{color:#94a3b8;background:transparent;cursor:not-allowed}
.fc-upsell-decimal-quantity-input{position:relative;justify-self:center;display:inline-flex;align-items:center;justify-content:center;gap:0;min-width:140px;max-width:180px;width:100%}
.fc-upsell-input-quantity-decimal{width:100%;max-width:180px;height:42px;border-radius:999px;padding:0 18px;text-align:center;font-size:16px;border:none;background:transparent;box-shadow:none}
.fc-upsell-input-overlay{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);font-size:16px;font-weight:600;color:#0f172a;pointer-events:none;white-space:nowrap;padding:0 6px;z-index:1}
.fc-upsell-input-overlay-target{color:transparent;caret-color:#0f172a}
.fc-upsell-input-overlay-target:focus{color:#0f172a}
.fc-upsell-decimal-quantity-input:focus-within .fc-upsell-input-overlay{opacity:0;visibility:hidden}
.fc-upsell-decimal-quantity-input:focus-within .fc-upsell-input-overlay-target{color:#0f172a}
.fc-upsell-decimal-quantity-chips{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px;justify-content:center;align-items:center}
.fc-upsell-chip{padding:8px 18px;border:1px solid #eaeaea;background:#fbfbfb;border-radius:999px;font-size:15px;line-height:1;transition:all .15s;box-shadow:0 2px 6px rgba(17,24,39,.06);cursor:pointer}
.fc-upsell-chip:hover{border-color:#ff6b6b;color:#ff6b6b;background:#fff}
.fc-upsell-decimal-quantity-info{font-size:13px;color:#0f172a;text-align:center;width:100%}
.fc-upsell-number-picker{margin-bottom:20px}
.fc-upsell-modal-label{font-size:15px;font-weight:600;color:#0f172a;margin-bottom:12px;display:block}
.fc-upsell-input-group-quantity{position:relative;display:flex;align-items:center}
.fc-upsell-input-number{font-size:15px;height:45px;width:100%;padding:10px 50px 10px 10px;text-align:center;border:1px solid #e2e8f0;border-radius:8px}
.fc-upsell-input-number:focus{outline:0;border-color:#ff6b6b}
.fc-upsell-btn-wrapper{position:absolute;top:0;right:0}
.fc-upsell-btn-number{position:absolute;right:0;width:40px;padding:0;line-height:18px;background:none;border:1px solid #e2e8f0;border-radius:0;cursor:pointer}
.fc-upsell-btn-number:hover{color:#ff6b6b}
.fc-upsell-btn-number[disabled]{color:#94a3b8;cursor:not-allowed}
.fc-upsell-btn-plus{top:0;height:24px;border-top-right-radius:8px}
.fc-upsell-btn-minus{top:23px;height:22px;border-bottom-right-radius:8px}
.fc-upsell-form-control{display:block;width:100%;padding:.375rem .75rem;font-size:1rem;font-weight:400;line-height:1.5;color:#212529;background-color:#fff;background-clip:padding-box;border:1px solid #ced4da;appearance:none;border-radius:.25rem;transition:border-color .15s ease-in-out,box-shadow .15s ease-in-out}
.fc-upsell-loading-overlay{position:absolute;inset:0;background:rgba(255,255,255,.7);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:15px;animation:fadeIn .3s ease}
.fc-upsell-spinner{width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #10b981;border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
.fc-upsell-loading-text{font-weight:700;color:#0f172a;font-size:.9375rem}
/* Bottom Sheet Modal Styles */
.fc-upsell-bottom-sheet-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:2147483645;display:flex;align-items:flex-end;justify-content:center;animation:fadeIn .2s ease;pointer-events:auto}
.fc-upsell-bottom-sheet{background:#fff;border-radius:24px 24px 0 0;width:100%;max-width:100%;max-height:90vh;overflow-y:auto;animation:slideUpSheet .3s cubic-bezier(.4,0,.2,1);position:relative;padding-bottom:env(safe-area-inset-bottom,20px)}
@keyframes slideUpSheet{from{transform:translateY(100%)}to{transform:translateY(0)}}
.fc-upsell-bottom-sheet-handle{width:40px;height:4px;background:#e2e8f0;border-radius:2px;margin:12px auto 8px}
.fc-upsell-bottom-sheet-header{display:flex;align-items:flex-start;justify-content:space-between;padding:12px 16px;gap:12px}
.fc-upsell-bottom-sheet-header-text{flex:1;min-width:0}
.fc-upsell-bottom-sheet-title{font-size:1rem;font-weight:700;color:#0f172a;margin:0;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.fc-upsell-bottom-sheet-subtitle{font-size:.75rem;color:#64748b;margin:2px 0 0;font-weight:500}
.fc-upsell-bottom-sheet-close{background:transparent;border:none;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#64748b;transition:all .2s;flex-shrink:0}
.fc-upsell-bottom-sheet-close:hover{background:#f1f5f9;color:#0f172a}
.fc-upsell-bottom-sheet-content{padding:12px 16px 16px}
.fc-upsell-bottom-sheet-label{font-size:.875rem;font-weight:600;color:#64748b;margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px}
/* Variant List (Mobile Optimized) */
.fc-upsell-variant-list{display:flex;flex-direction:column;gap:6px;max-height:280px;overflow-y:auto;margin-bottom:0}
.fc-upsell-variant-item{display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f8fafc;border:2px solid transparent;border-radius:10px;cursor:pointer;transition:all .2s;text-align:left;width:100%}
.fc-upsell-variant-item:hover{background:#f1f5f9}
.fc-upsell-variant-item.active{background:#eff6ff;border-color:#3b82f6}
.fc-upsell-variant-item img{width:48px;height:48px;object-fit:cover;border-radius:8px;flex-shrink:0}
.fc-upsell-variant-item span{flex:1;font-size:.9375rem;font-weight:500;color:#0f172a}
.fc-upsell-variant-check{color:#3b82f6;flex-shrink:0}
/* Quantity Controls */
.fc-upsell-bottom-sheet-quantity{margin-bottom:20px}
.fc-upsell-bottom-sheet-qty-controls{display:flex;align-items:center;justify-content:center;gap:16px;margin-bottom:16px}
.fc-upsell-bottom-sheet-qty-btn{width:48px;height:48px;border-radius:50%;border:2px solid #e2e8f0;background:#fff;font-size:1.5rem;font-weight:600;color:#0f172a;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s}
.fc-upsell-bottom-sheet-qty-btn:hover:not(:disabled){border-color:#3b82f6;color:#3b82f6;background:#eff6ff}
.fc-upsell-bottom-sheet-qty-btn:disabled{opacity:.4;cursor:not-allowed}
.fc-upsell-bottom-sheet-qty-display{min-width:100px;text-align:center}
.fc-upsell-bottom-sheet-qty-value{font-size:2rem;font-weight:800;color:#0f172a}
.fc-upsell-bottom-sheet-qty-suffix{font-size:.875rem;font-weight:500;color:#64748b;margin-left:4px}
.fc-upsell-bottom-sheet-chips{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
.fc-upsell-bottom-sheet-chip{padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:20px;font-size:.875rem;font-weight:500;color:#64748b;cursor:pointer;transition:all .2s}
.fc-upsell-bottom-sheet-chip:hover{border-color:#3b82f6;color:#3b82f6}
/* Summary */
.fc-upsell-bottom-sheet-summary{background:#f8fafc;border-radius:12px;padding:16px;margin-bottom:20px}
.fc-upsell-bottom-sheet-summary-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0}
.fc-upsell-bottom-sheet-summary-row span:first-child{font-size:.875rem;color:#64748b}
.fc-upsell-bottom-sheet-summary-row span:last-child{font-size:1rem;font-weight:600;color:#0f172a}
.fc-upsell-bottom-sheet-summary-row.total{border-top:1px solid #e2e8f0;margin-top:8px;padding-top:16px}
.fc-upsell-bottom-sheet-summary-row.total span:first-child{font-weight:600;color:#0f172a}
.fc-upsell-bottom-sheet-summary-row.total span:last-child{font-size:1.25rem;font-weight:800;color:#10b981}
/* Actions */
.fc-upsell-bottom-sheet-actions{display:flex;gap:10px;margin-top:16px;padding:0 16px 8px}
.fc-upsell-bottom-sheet-btn{flex:1;padding:14px 20px;border:none;border-radius:12px;font-size:.9375rem;font-weight:600;cursor:pointer;transition:all .2s;text-align:center}
.fc-upsell-bottom-sheet-btn.secondary{background:#f1f5f9;color:#64748b}
.fc-upsell-bottom-sheet-btn.secondary:hover{background:#e2e8f0}
.fc-upsell-bottom-sheet-btn.primary{background:#0f172a;color:#fff}
.fc-upsell-bottom-sheet-btn.primary:hover:not(:disabled){background:#1e293b;transform:translateY(-1px)}
.fc-upsell-bottom-sheet-btn:disabled{opacity:.5;cursor:not-allowed}
/* Desktop: Center modal instead of bottom sheet */
@media (min-width:768px){
.fc-upsell-bottom-sheet-overlay{align-items:center;padding:24px}
.fc-upsell-bottom-sheet{border-radius:24px;max-width:480px;animation:modalScaleUp .4s cubic-bezier(.34,1.56,.64,1)}
.fc-upsell-bottom-sheet-handle{display:none}
.fc-upsell-variant-list{flex-direction:row;flex-wrap:wrap;gap:12px}
.fc-upsell-variant-item{flex:0 0 calc(50% - 6px);padding:16px}
}
/* Unit Decimal Quantity Picker (Product Detail Style) */
.fc-upsell-unit-picker{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:20px}
.fc-upsell-unit-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.fc-upsell-unit-title{font-size:.9375rem;font-weight:600;color:#0f172a}
.fc-upsell-unit-desc{font-size:.75rem;color:#64748b}
.fc-upsell-unit-main{display:flex;align-items:center;justify-content:center;gap:0;background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-bottom:12px}
.fc-upsell-unit-btn{width:44px;height:44px;border:none;background:transparent;font-size:1.25rem;font-weight:500;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
.fc-upsell-unit-btn:hover:not(:disabled){color:#0f172a;background:#f1f5f9}
.fc-upsell-unit-btn:disabled{opacity:.3;cursor:not-allowed}
.fc-upsell-unit-input-wrap{flex:1;display:flex;align-items:center;justify-content:center;position:relative;border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0}
.fc-upsell-unit-input{width:100%;height:44px;border:none;background:transparent;text-align:center;font-size:1rem;font-weight:600;color:#0f172a;padding:0 30px 0 8px;outline:none}
.fc-upsell-unit-input:focus{background:#f8fafc}
.fc-upsell-unit-suffix{position:absolute;right:8px;font-size:.875rem;color:#64748b;pointer-events:none}
.fc-upsell-unit-chips{display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin-bottom:8px}
.fc-upsell-unit-chip{padding:6px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:.8125rem;font-weight:500;color:#64748b;cursor:pointer;transition:all .15s}
.fc-upsell-unit-chip:hover:not(:disabled){border-color:#3b82f6;color:#3b82f6}
.fc-upsell-unit-chip:disabled{opacity:.4;cursor:not-allowed}
.fc-upsell-unit-info{font-size:.75rem;color:#64748b;text-align:center}
/* Regular Quantity Picker (Minimal Style) */
.fc-upsell-qty-picker{margin-bottom:20px}
.fc-upsell-qty-label{display:block;font-size:.875rem;font-weight:600;color:#64748b;margin-bottom:8px}
.fc-upsell-qty-group{position:relative;display:flex;align-items:center}
.fc-upsell-qty-input{width:100%;height:44px;border:1px solid #e2e8f0;border-radius:8px;text-align:center;font-size:1rem;font-weight:600;color:#0f172a;padding:0 44px 0 12px;outline:none;transition:border-color .15s}
.fc-upsell-qty-input:focus{border-color:#3b82f6}
.fc-upsell-qty-btns{position:absolute;right:0;top:0;height:100%;display:flex;flex-direction:column}
.fc-upsell-qty-btn-sm{width:36px;flex:1;border:none;border-left:1px solid #e2e8f0;background:#fff;font-size:.875rem;font-weight:500;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
.fc-upsell-qty-btn-sm.plus{border-radius:0 8px 0 0;border-bottom:1px solid #e2e8f0}
.fc-upsell-qty-btn-sm.minus{border-radius:0 0 8px 0}
.fc-upsell-qty-btn-sm:hover:not(:disabled){background:#f1f5f9;color:#0f172a}
.fc-upsell-qty-btn-sm:disabled{opacity:.3;cursor:not-allowed}
/* Decimal Quantity Card (Product Detail Style) */
.fc-upsell-bottom-sheet .decimal-quantity-card{flex-direction:column;align-items:flex-start;background:#fff;border:1px solid #ededed;border-radius:12px;padding:14px;display:flex;gap:10px;width:100%;max-width:100%;box-shadow:0 2px 8px rgba(17,24,39,.04)}
.fc-upsell-bottom-sheet .decimal-quantity-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;width:100%}
.fc-upsell-bottom-sheet .decimal-quantity-title{font-size:15px;font-weight:600;margin-bottom:4px;color:#0f172a}
.fc-upsell-bottom-sheet .decimal-quantity-desc{font-size:13px;color:#64748b;line-height:1.4}
.fc-upsell-bottom-sheet .decimal-quantity-main{display:grid;grid-template-columns:44px 1fr 44px;align-items:center;gap:0;width:100%;max-width:280px;margin:10px auto 4px;border:1px solid #e6e6e6;border-radius:999px;background:#fff}
.fc-upsell-bottom-sheet .btn-quantity{height:44px;width:44px;border-radius:999px!important;border:none!important;background:transparent!important;line-height:1;font-size:20px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;color:#0f172a}
.fc-upsell-bottom-sheet .btn-quantity:hover{color:#3b82f6}
.fc-upsell-bottom-sheet .btn-quantity[disabled]{color:#94a3b8;cursor:not-allowed}
.fc-upsell-bottom-sheet .decimal-quantity-input{position:relative;justify-self:center;display:inline-flex;align-items:center;justify-content:center;gap:0;width:100%}
.fc-upsell-bottom-sheet .input-quantity-decimal{width:100%;height:42px;border-radius:999px;padding:0 18px;text-align:center;font-size:16px;border:none;background:transparent;box-shadow:none;outline:none}
.fc-upsell-bottom-sheet .input-suffix{display:none}
.fc-upsell-bottom-sheet .input-overlay{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);font-size:16px;font-weight:600;color:#0f172a;pointer-events:none;white-space:nowrap;padding:0 6px;z-index:1}
.fc-upsell-bottom-sheet .input-overlay-target{color:transparent;caret-color:#0f172a}
.fc-upsell-bottom-sheet .decimal-quantity-input:focus-within .input-overlay{opacity:0;visibility:hidden}
.fc-upsell-bottom-sheet .decimal-quantity-input:focus-within .input-overlay-target{color:#0f172a}
.fc-upsell-bottom-sheet .decimal-quantity-chips{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px;justify-content:center;align-items:center}
.fc-upsell-bottom-sheet .chip{padding:8px 18px;border:1px solid #eaeaea;background:#fbfbfb;border-radius:999px;font-size:15px;line-height:1;cursor:pointer;box-shadow:0 2px 6px rgba(17,24,39,.06)}
.fc-upsell-bottom-sheet .chip:hover{border-color:#3b82f6;color:#3b82f6;background:#fff}
.fc-upsell-bottom-sheet .chip[disabled]{opacity:.5;cursor:not-allowed;pointer-events:none}
.fc-upsell-bottom-sheet .decimal-quantity-info{font-size:13px;color:#0f172a;text-align:center;width:100%}
/* Number Picker (Compact Horizontal Style) */
.fc-upsell-bottom-sheet .number-picker-lg{display:flex;flex-direction:column;align-items:center;gap:10px;width:100%}
.fc-upsell-bottom-sheet .number-picker-lg label{font-weight:600;margin:0;font-size:14px;color:#0f172a}
.fc-upsell-bottom-sheet .input-group-quantity{display:inline-flex;align-items:center;border:2px solid #cbd5e1;border-radius:10px;overflow:hidden;background:#fff}
.fc-upsell-bottom-sheet .input-group-quantity .input-quantity{font-size:16px;font-weight:600;height:44px;width:56px;padding:0;text-align:center;border:none;background:transparent;outline:none;-moz-appearance:textfield}
.fc-upsell-bottom-sheet .input-group-quantity .input-quantity::-webkit-outer-spin-button,.fc-upsell-bottom-sheet .input-group-quantity .input-quantity::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.fc-upsell-bottom-sheet .btn-number{width:40px;height:44px;border:none;background:#f1f5f9;font-size:18px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#475569;transition:all .15s}
.fc-upsell-bottom-sheet .btn-minus{border-right:2px solid #cbd5e1}
.fc-upsell-bottom-sheet .btn-plus{border-left:2px solid #cbd5e1}
.fc-upsell-bottom-sheet .btn-number:hover{background:#e2e8f0;color:#0f172a}
.fc-upsell-bottom-sheet .btn-number[disabled]{color:#cbd5e1;cursor:not-allowed;background:#f8fafc}
        </style>

        @stack('styles')

        {!! setting('custom_header_assets') !!}

        <script>
            window.FleetCart = {
                baseUrl: '{{ localized_url(locale(), url('/')) }}',
                rtl: {{ is_rtl() ? 'true' : 'false' }},
                storeName: '{{ setting('store_name') }}',
                storeLogo: '{{ $logo }}',
                productsPageSlug: '{{ setting('products_page_slug', 'products') }}',
                currency: '{{ currency() }}',
                locale: '{{ locale() }}',
                supportedLocales: @json(supported_locales()),
                loggedIn: {{ auth()->check() ? 'true' : 'false' }},
                compareCount: {{ $compareCount }},
                cartQuantity: {{ $cartQuantity }},
                wishlistCount: {{ $wishlistCount }},
                csrfToken: '{{ csrf_token() }}',
                freeShippingEnabled: {{ setting('smart_shipping_enabled') === '1' && setting('smart_shipping_show_progress_bar') === '1' ? 'true' : 'false' }},
                freeShippingMinAmount: {{ (float) setting('smart_shipping_free_threshold', 0) }},
                smartShippingButton1Text: '{{ setting('smart_shipping_button_1_text', 'Yeni Gelenler') }}',
                smartShippingButton1Link: '{{ setting('smart_shipping_button_1_link', url('products?sort=latest')) }}',
                smartShippingButton2Text: '{{ setting('smart_shipping_button_2_text', 'Fırsat Ürünleri') }}',
                smartShippingButton2Link: '{{ setting('smart_shipping_button_2_link', url('products?sort=sales')) }}',
                data: {},
                langs: {
                    'storefront::storefront.something_went_wrong': '{{ trans('storefront::storefront.something_went_wrong') }}',
                    'storefront::layouts.more_results': '{{ trans('storefront::layouts.more_results') }}'
                },
            };
        </script>

        {!! $schemaMarkup->toScript() !!}

        @stack('globals')
    </head>

    <body
        dir="{{ is_rtl() ? 'rtl' : 'ltr' }}"
        class="page-template {{ is_rtl() ? 'rtl' : 'ltr' }}"
        data-theme-color="{{ $themeColor->toHexString() }}"
        x-data="App"
        :class="{ 'has-sidebar-open': $store.layout.overlay }"
    >
        {{-- Google Tag Manager (noscript) - SEO Critical --}}
        @if(setting('google_tag_manager_id'))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ setting('google_tag_manager_id') }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        @endif

        <div class="wrapper">
            @include('storefront::public.layouts.top_nav')
            @include('storefront::public.layouts.header_custom_text')
            @include('storefront::public.layouts.header')
            @include('storefront::public.layouts.navigation')
            @include('storefront::public.layouts.breadcrumb')

            <main role="main">
                @yield('content')
            </main>

            @include('storefront::public.home.sections.newsletter_subscription')
            @include('storefront::public.layouts.footer')

            <div
                class="overlay"
                :class="{ active: $store.layout.overlay }"
                @click="hideOverlay"
            >
            </div>

            @include('storefront::public.layouts.sidebar_menu')
            @include('storefront::public.layouts.localization')

            @if (!request()->routeIs('checkout.create'))
                @include('storefront::public.layouts.sidebar_cart')
            @endif

            @include('storefront::public.layouts.alert')
        </div>

        @stack('pre-scripts')
        @stack('scripts')

        {!! setting('custom_footer_assets') !!}

        @if(file_exists(resource_path('../modules/Analytics/Resources/views/consent-banner.blade.php')))
            @include('analytics::consent-banner')
        @endif

        @if (app()->environment('local') && app()->bound('debugbar'))
            {!! app('debugbar')->render() !!}
        @endif
    </body>
</html>
