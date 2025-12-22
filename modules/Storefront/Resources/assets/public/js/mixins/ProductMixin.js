export default function (product) {
    return {
        product: product,
        item: product.variant || product,
        addingToCart: false,

        get productName() {
            if (this.product.list_variants_separately && this.hasAnyVariant && this.item?.name) {
                return `${this.product.name} - ${this.item.name}`;
            }
            return this.product.name;
        },

        get productUrl() {
            let url = `/products/${this.product.slug}`;

            if (this.hasAnyVariant && this.item.uid) {
                url += `?variant=${this.item.uid}`;
            }

            return url;
        },

        get unitSuffix() {
            return this.item.unit_suffix || this.product.unit_suffix || null;
        },

        get productPrice() {
            if (this.hasSpecialPrice) {
                const sp = formatCurrency(this.specialPrice);
                const rp = formatCurrency(this.regularPrice);
                const suf = this.unitSuffix ? ` /${this.unitSuffix}` : "";
                return `<span class='special-price'>${sp}${suf}</span> <span class='previous-price'>${rp}${suf}</span>`;
            }

            const rp = formatCurrency(this.regularPrice);
            const suf = this.unitSuffix ? ` /${this.unitSuffix}` : "";
            return `${rp}${suf}`;
        },

        get regularPrice() {
            return this.item.price.inCurrentCurrency.amount;
        },

        get hasSpecialPrice() {
            return this.item.special_price !== null;
        },

        get hasPercentageSpecialPrice() {
            return this.item.has_percentage_special_price;
        },

        get specialPrice() {
            return this.item.selling_price.inCurrentCurrency.amount;
        },

        get specialPricePercent() {
            return Math.round(
                ((this.regularPrice - this.specialPrice) / this.regularPrice) *
                    100
            );
        },

        get hasAnyVariant() {
            return this.product.variant !== null;
        },

        get hasAnyOption() {
            return this.product.options_count > 0;
        },

        get hasNoOption() {
            return !this.hasAnyOption;
        },

        get hasAnyMedia() {
            return this.item.media.length !== 0;
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
            return `${window.location.origin}/build/assets/image-placeholder.png`;
        },

        get baseImageThumb() {
            const p = this.product?.base_image_thumb?.path;
            const v = this.item?.base_image_thumb?.path;
            return p || v || null;
        },

        get isInStock() {
            return this.item.is_in_stock;
        },

        get isOutOfStock() {
            return this.item.is_out_of_stock;
        },

        get doesManageStock() {
            return this.item.does_manage_stock;
        },

        get isNew() {
            return !this.isOutOfStock && this.product.is_new;
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
