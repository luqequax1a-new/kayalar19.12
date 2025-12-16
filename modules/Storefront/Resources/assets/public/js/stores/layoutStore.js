Alpine.store("layout", {
    sidebarMenuOpen: false,
    sidebarCartOpen: false,
    sidebarFilterOpen: false,
    localizationMenuOpen: false,
    get overlay() {
        return (
            this.sidebarMenuOpen ||
            this.sidebarCartOpen ||
            this.sidebarFilterOpen ||
            this.localizationMenuOpen
        );
    },

    get isOpenSidebarMenu() {
        return this.sidebarMenuOpen;
    },

    get isOpenSidebarCart() {
        return this.sidebarCartOpen;
    },

    get isOpenSidebarFilter() {
        return this.sidebarFilterOpen;
    },

    get isOpenlocalizationMenu() {
        return this.localizationMenuOpen;
    },

    openSidebarMenu() {
        this.sidebarMenuOpen = true;
    },

    closeSidebarMenu() {
        this.sidebarMenuOpen = false;
    },

    openSidebarCart(event) {
        if (event) {
            event.preventDefault();
        }

        if (window.location.pathname.endsWith("/checkout")) {
            window.location.href = "/cart";

            return;
        }

        if (Alpine.store("cart").fetching) {
            return;
        }

        this.sidebarCartOpen = true;
    },

    closeSidebarCart() {
        this.sidebarCartOpen = false;
    },

    openSidebarFilter() {
        this.sidebarFilterOpen = true;
    },

    closeSidebarFilter() {
        this.sidebarFilterOpen = false;
    },

    openLocalizationMenu() {
        this.localizationMenuOpen = true;
    },

    closeLocalizationMenu() {
        this.localizationMenuOpen = false;
    },
});
