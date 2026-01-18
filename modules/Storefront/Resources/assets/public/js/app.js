import { trans, formatCurrency } from "./functions";
import { notify } from "./components/Toaster";
import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import $ from "jquery";
import * as bootstrap from "bootstrap/dist/js/bootstrap.js";
import "./vendors/axios";
import registerCartUpsellBox from "./components/CartUpsellBox";

Alpine.plugin(collapse);

window.Alpine = Alpine;
window.bootstrap = bootstrap;
window.$ = window.jQuery = $;
window.trans = trans;
window.formatCurrency = formatCurrency;
window.notify = notify;

// Register CartUpsellBox component globally
if (typeof registerCartUpsellBox === "function") {
    registerCartUpsellBox(Alpine);
}

Alpine.data("App", () => ({
    showCouponList: false,

    hideOverlay() {
        const layoutStore = this.$store.layout;

        layoutStore.closeSidebarMenu();
        layoutStore.closeSidebarCart();
        layoutStore.closeSidebarFilter();
        layoutStore.closeLocalizationMenu();
    },
}));

