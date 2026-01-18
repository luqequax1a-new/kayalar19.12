export default function (product) {
    return {
        product: product,
        item: product.variant || product,
        addingToCart: false,

        get productName() {
            // Priority 1: Separately listed variants
            if (this.product.list_variants_separately && this.hasAnyVariant && this.item?.name) {
                return `${this.product.name} - ${this.item.name}`;
            }

            // Priority 2: Currently selected/hovered variant name (if available)
            if (this.hasAnyVariant && this.item && this.item.name && this.item.uid && this.item.uid !== this.product.uid) {
                return `${this.product.name} - ${this.item.name}`;
            }

            return this.product.name;
        },

        get productUrl() {
            // İkas-style: Use backend URL or clean format
            let url = this.product.url || `/${this.product.slug}`;

            if (this.hasAnyVariant && this.item.uid) {
                url += `?variant=${this.item.uid}`;
            }

            return url;
        },

        get unitSuffix() {
            return this.item.unit_suffix || this.product.unit_suffix || null;
        },

        get productPrice() {
            const suf = this.unitSuffix ? `/${this.unitSuffix}` : "";

            if (this.hasSpecialPrice) {
                const sp = formatCurrency(this.specialPrice);
                const rp = formatCurrency(this.regularPrice);
                return `<span class='special-price'>${sp}${suf}</span><span class='previous-price'>${rp}${suf}</span>`;
            }

            const rp = formatCurrency(this.regularPrice);
            return `<span class='current-price'>${rp}${suf}</span>`;
        },

        get regularPrice() {
            const price = this.item?.price;
            if (!price) return 0;
            return price.inCurrentCurrency?.amount ?? price.amount ?? 0;
        },

        get hasSpecialPrice() {
            return this.item?.special_price !== null && this.item?.special_price !== undefined;
        },

        get hasPercentageSpecialPrice() {
            return !!this.item?.has_percentage_special_price;
        },

        get specialPrice() {
            const price = this.item?.selling_price;
            if (!price) return this.regularPrice;
            return price.inCurrentCurrency?.amount ?? price.amount ?? 0;
        },

        get specialPricePercent() {
            return Math.round(
                ((this.regularPrice - this.specialPrice) / this.regularPrice) *
                100
            );
        },

        get hasAnyVariant() {
            return !!this.product.variant;
        },

        get hasAnyOption() {
            return this.product.options_count > 0;
        },

        get hasNoOption() {
            return !this.hasAnyOption;
        },

        get hasAnyMedia() {
            return (this.item?.media?.length || 0) !== 0;
        },

        get hasBaseImage() {
            const p = this.product?.base_image?.path;
            const v = this.item?.base_image?.path;
            return !!(p || v);
        },

        get baseImage() {
            // Prefer listing/grid variant when available to avoid blurry upscaling.
            const pt = this.product?.base_image_thumb?.path;
            const vt = this.item?.base_image_thumb?.path;
            const p = this.product?.base_image?.path;
            const v = this.item?.base_image?.path;

            if (pt) return pt;
            if (vt) return vt;
            if (p) return p;
            if (v) return v;
            return `${window.location.origin}/measurements/image-placeholder.png`;
        },

        get baseImageThumb() {
            const p = this.product?.base_image_thumb?.path;
            const v = this.item?.base_image_thumb?.path;
            return p || v || null;
        },

        get isInStock() {
            return !!this.item?.is_in_stock;
        },

        get isOutOfStock() {
            return !!this.item?.is_out_of_stock;
        },

        get doesManageStock() {
            return !!this.item?.does_manage_stock;
        },

        get isNew() {
            return !this.isOutOfStock && !!this.product?.is_new;
        },

        syncWishlist() {
            this.$store.wishlist.syncWishlist(this.product.id);
        },

        syncCompareList() {
            this.$store.compare.syncCompareList(this.product.id);
        },

        addToCart() {
            if (this.addingToCart) {
                return;
            }

            this.addingToCart = true;

            let url = `/cart/items?product_id=${this.product.id}&qty=${1}`;

            if (this.hasAnyVariant) {
                url += `&variant_id=${this.item.id}`;
            }

            axios
                .post(url)
                .then((response) => {
                    this.$store.cart.updateCart(response.data);
                    this.$store.layout.openSidebarCart();
                })
                .catch((error) => {
                    notify(error.response.data.message);
                })
                .finally(() => {
                    this.addingToCart = false;
                });
        },
    };
}
import { formatCurrency } from "../functions";
