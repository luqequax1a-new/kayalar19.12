<div class="social-share" x-data="{ 
    copyDone: false,
    shareOnWhatsApp() {
        const text = `{{ $product->name }} - ${FleetCart.baseUrl}${this.productUrl}`;
        const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
        window.open(url, '_blank');
    },
    contactWhatsApp() {
        const phone = '{{ setting('phone_number') }}';
        const msg = `Merhaba, bu ürün hakkında bilgi alabilir miyim?\n\n{{ $product->name }}\n${FleetCart.baseUrl}${this.productUrl}`;
        const url = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`;
        window.open(url, '_blank');
    },
    copyToClipboard() {
        const url = `${FleetCart.baseUrl}${this.productUrl}`;
        navigator.clipboard.writeText(url).then(() => {
            this.copyDone = true;
            setTimeout(() => this.copyDone = false, 2000);
        });
    }
}">
    <span>{{ trans('storefront::product.share') }}</span>

    <ul class="list-inline social-links d-flex">
        <li>
            <a
                href="javascript:void(0)"
                class="whatsapp"
                @click="shareOnWhatsApp"
                title="WhatsApp'ta Paylaş"
            >
                <i class="lab la-whatsapp"></i>
            </a>
        </li>

        <li>
            <a
                href="javascript:void(0)"
                class="whatsapp-business"
                @click="contactWhatsApp"
                title="WhatsApp Destek"
            >
                <i class="las la-headset"></i>
            </a>
        </li>

        <li>
            <a
                :href="`https://www.facebook.com/sharer.php?u=${FleetCart.baseUrl}${productUrl}`"
                class="facebook"
                title="{{ trans('storefront::product.facebook') }}"
                target="_blank"
            >
                <i class="lab la-facebook-f"></i>
            </a>
        </li>

        <li>
            <a
                :href="`https://twitter.com/share?url=${FleetCart.baseUrl}${productUrl}&text={{ $product->name }}`"
                class="twitter"
                title="{{ trans('storefront::product.twitter') }}"
                target="_blank"
            >
                <i class="lab la-twitter"></i>
            </a>
        </li>

        <li>
            <a
                :href="`https://pinterest.com/pin/create/button/?url=${FleetCart.baseUrl}${productUrl}&media=${typeof baseImage !== 'undefined' ? baseImage : ''}&description={{ $product->name }}`"
                class="pinterest"
                title="Pinterest"
                target="_blank"
            >
                <i class="lab la-pinterest-p"></i>
            </a>
        </li>

        <li>
            <a
                href="javascript:void(0)"
                class="copy-link"
                @click="copyToClipboard"
                title="Bağlantıyı Kopyala"
            >
                <i class="las" :class="copyDone ? 'la-check' : 'la-link'"></i>
            </a>
        </li>
    </ul>
</div>
