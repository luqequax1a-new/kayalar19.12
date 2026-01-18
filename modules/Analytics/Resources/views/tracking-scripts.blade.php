{{-- Google Tag Manager --}}
@if(setting('google_tag_manager_id'))
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ setting('google_tag_manager_id') }}');
</script>
@endif

{{-- Google Analytics GA4 (only if GTM is not enabled) --}}
@if(!setting('google_tag_manager_id') && setting('google_analytics_measurement_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_measurement_id') }}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{{ setting('google_analytics_measurement_id') }}', {
    'send_page_view': true,
    'cookie_flags': 'SameSite=None;Secure'
});
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

{{-- E-commerce Event Tracking Helper --}}
<script>
window.FleetCartAnalytics = {
    // Track product view
    trackProductView: function(product) {
        if (typeof gtag === 'function') {
            gtag('event', 'view_item', {
                currency: '{{ currency() }}',
                value: product.price,
                items: [{
                    item_id: product.id,
                    item_name: product.name,
                    price: product.price,
                    quantity: 1
                }]
            });
        }
        
        if (typeof fbq === 'function') {
            fbq('track', 'ViewContent', {
                content_ids: [product.id],
                content_name: product.name,
                content_type: 'product',
                value: product.price,
                currency: '{{ currency() }}'
            });
        }
        
        if (typeof ttq === 'function') {
            ttq.track('ViewContent', {
                content_id: product.id,
                content_name: product.name,
                content_type: 'product',
                value: product.price,
                currency: '{{ currency() }}'
            });
        }
    },
    
    // Track add to cart
    trackAddToCart: function(product, quantity) {
        quantity = quantity || 1;
        var value = product.price * quantity;
        
        if (typeof gtag === 'function') {
            gtag('event', 'add_to_cart', {
                currency: '{{ currency() }}',
                value: value,
                items: [{
                    item_id: product.id,
                    item_name: product.name,
                    price: product.price,
                    quantity: quantity
                }]
            });
        }
        
        if (typeof fbq === 'function') {
            fbq('track', 'AddToCart', {
                content_ids: [product.id],
                content_name: product.name,
                content_type: 'product',
                value: value,
                currency: '{{ currency() }}'
            });
        }
        
        if (typeof ttq === 'function') {
            ttq.track('AddToCart', {
                content_id: product.id,
                content_name: product.name,
                content_type: 'product',
                value: value,
                currency: '{{ currency() }}'
            });
        }
    },
    
    // Track begin checkout
    trackBeginCheckout: function(cart) {
        if (typeof gtag === 'function') {
            gtag('event', 'begin_checkout', {
                currency: cart.currency,
                value: cart.total,
                items: cart.items
            });
        }
        
        if (typeof fbq === 'function') {
            fbq('track', 'InitiateCheckout', {
                content_ids: cart.items.map(function(item) { return item.item_id; }),
                contents: cart.items,
                value: cart.total,
                currency: cart.currency,
                num_items: cart.items.length
            });
        }
        
        if (typeof ttq === 'function') {
            ttq.track('InitiateCheckout', {
                contents: cart.items,
                value: cart.total,
                currency: cart.currency
            });
        }
    },
    
    // Track purchase
    trackPurchase: function(order) {
        if (typeof gtag === 'function') {
            gtag('event', 'purchase', {
                transaction_id: order.id,
                value: order.total,
                tax: order.tax,
                shipping: order.shipping,
                currency: order.currency,
                items: order.items
            });
        }
        
        if (typeof fbq === 'function') {
            fbq('track', 'Purchase', {
                content_ids: order.items.map(function(item) { return item.item_id; }),
                contents: order.items,
                value: order.total,
                currency: order.currency,
                num_items: order.items.length
            });
        }
        
        if (typeof ttq === 'function') {
            ttq.track('CompletePayment', {
                contents: order.items,
                value: order.total,
                currency: order.currency
            });
        }
    },
    
    // Track search
    trackSearch: function(searchTerm) {
        if (typeof gtag === 'function') {
            gtag('event', 'search', {
                search_term: searchTerm
            });
        }
        
        if (typeof fbq === 'function') {
            fbq('track', 'Search', {
                search_string: searchTerm
            });
        }
        
        if (typeof ttq === 'function') {
            ttq.track('Search', {
                query: searchTerm
            });
        }
    }
};
</script>
