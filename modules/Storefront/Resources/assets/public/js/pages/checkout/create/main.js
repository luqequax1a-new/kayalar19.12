import Errors from "../../../components/Errors";
import "../../../components/CartItem";
import sehirler from "@modules/../sehirler.json";
import ilceler from "@modules/../ilceler.json";

function trTitleCase(name) {
    if (name === null || name === undefined) return name;
    const mapUpperToLower = { I: "ı", İ: "i", Ç: "ç", Ş: "ş", Ğ: "ğ", Ü: "ü", Ö: "ö" };
    let s = String(name);
    s = s.replace(/[IİÇŞĞÜÖ]/g, (ch) => mapUpperToLower[ch] || ch);
    s = s.toLowerCase();
    const mapLowerToUpper = { i: "İ", ı: "I", ç: "Ç", ş: "Ş", ğ: "Ğ", ü: "Ü", ö: "Ö" };
    return s
        .split(/([\s\-]+)/)
        .map((part, idx) => {
            if (idx % 2 === 1) return part;
            if (!part) return part;
            const first = part.charAt(0);
            const rest = part.slice(1);
            const firstU = mapLowerToUpper[first] || first.toUpperCase();
            return firstU + rest;
        })
        .join("");
}

const SEHIRLER = sehirler.map((s) => ({ ...s, sehir_adi: trTitleCase(s.sehir_adi) }));
const ILCELER = ilceler.map((d) => ({ ...d, sehir_adi: trTitleCase(d.sehir_adi), ilce_adi: trTitleCase(d.ilce_adi) }));


Alpine.data(
    "Checkout",
    ({
        customerEmail,
        customerPhone,
        addresses,
        defaultAddress,
        gateways,
        countries,
        selectedPaymentMethod,
        selectedShippingMethod,
    }) => ({
        addresses,
        defaultAddress,
        gateways,
        countries,
        form: {
            customer_email: customerEmail,
            customer_phone: customerPhone,
            billing: {
                company_name: "",
                tax_number: "",
                tax_office: "",
                phone: "",
                billing_email: "",
                city_id: null,
                district_id: null,
                address_line: "",
            },
            shipping: {
                first_name: "",
                last_name: "",
                phone: "",
                city_id: null,
                district_id: null,
                address_line: "",
            },
            billingAddressId: null,
            shippingAddressId: null,
            newBillingAddress: false,
            newShippingAddress: false,
            ship_to_a_different_address: false,
            invoice: {
                title: "",
                tax_office: "",
                tax_number: "",
            },
            password: "",
            create_an_account: false,
        },
        states: {
            billing: {},
            shipping: {},
        },
        districts: {
            billing: [],
            shipping: [],
        },
        shippingDistricts: [],
        billingDistricts: [],
        provincesTR: SEHIRLER,
        currentProvinceTRBilling: null,
        currentProvinceTRShipping: null,
        controller: null,
        shippingMethodName: null,
        selectedShippingMethod: selectedShippingMethod || null,
        applyingCoupon: false,
        showCouponList: false,
        couponCode: null,

        couponError: null,
        placingOrder: false,
        stripe: null,
        stripeElements: null,
        authorizeNetToken: null,
        payFastFormFields: {},
        errors: new Errors(),
        lastPaymentMethod: selectedPaymentMethod || null,
        updateController: null,

        normalizeTR(s) {
            if (!s) return s;
            const map = { İ: 'I', ı: 'I', i: 'I', I: 'I', Ç: 'C', ç: 'c', Ş: 'S', ş: 's', Ğ: 'G', ğ: 'g', Ü: 'U', ü: 'u', Ö: 'O', ö: 'o' };
            const t = String(s)
                .replace(/[İıiIÇçŞşĞğÜüÖö]/g, (m) => map[m])
                .normalize('NFD').replace(/\p{Diacritic}/gu, '')
                .toUpperCase();
            return t;
        },

        get cartFetched() {
            return this.$store.cart.fetched;
        },

        get cart() {
            return this.$store.cart.cart;
        },

        get cartIsEmpty() {
            return this.$store.cart.isEmpty;
        },

        get hasAddress() {
            return Object.keys(this.addresses).length !== 0;
        },

        get shippingAddresses() {
            return Object.values(this.addresses || {}).filter((a) => a && a.type === "shipping");
        },

        get billingAddresses() {
            return Object.values(this.addresses || {}).filter((a) => a && a.type === "billing");
        },

        get hasShippingAddress() {
            return this.shippingAddresses.length !== 0;
        },

        get hasBillingAddress() {
            return this.billingAddresses.length !== 0;
        },

        get firstCountry() {
            return Object.keys(this.countries)[0];
        },

        get singleCountry() {
            return Object.keys(this.countries).length === 1;
        },

        get hasBillingStates() {
            return Object.keys(this.states.billing).length !== 0;
        },

        get hasShippingStates() {
            return Object.keys(this.states.shipping).length !== 0;
        },

        get hasNoPaymentMethod() {
            return Object.keys(this.gateways).length === 0;
        },

        get firstPaymentMethod() {
            return Object.keys(this.gateways)[0];
        },

        get shouldShowPaymentInstructions() {
            return ["bank_transfer", "check_payment"].includes(
                this.form.payment_method
            );
        },

        get paymentInstructions() {
            if (this.shouldShowPaymentInstructions) {
                return this.gateways[this.form.payment_method].instructions;
            }
        },

        get hasShippingMethod() {
            // Show shipping cost only after a method has been selected and
            // persisted on the cart, not merely when options exist.
            return Boolean(this.cart.shippingMethodName);
        },

        get hasFreeShipping() {
            return this.cart.coupon?.free_shipping ?? false;
        },

        get firstShippingMethod() {
            return Object.keys(this.cart.availableShippingMethods)[0];
        },

        init() {
            console.log('[CHECKOUT] Initializing...');
            Alpine.effect(() => {
                if (this.cartFetched) {
                    console.log('[CHECKOUT] cartFetched=true');
                    this.hideSkeleton();
                    const keys = Object.keys(this.gateways || {});
                    const hasSaved = this.lastPaymentMethod && keys.includes(this.lastPaymentMethod);
                    if (hasSaved) {
                        this.changePaymentMethod(this.lastPaymentMethod);
                    } else {
                        this.changePaymentMethod(this.firstPaymentMethod);
                        this.lastPaymentMethod = this.firstPaymentMethod;
                    }

                    if (this.cart.shippingMethodName) {
                        this.changeShippingMethod(this.cart.shippingMethodName);
                    } else if (this.selectedShippingMethod) {
                        this.updateShippingMethod(this.selectedShippingMethod);
                    } else {
                        this.updateShippingMethod(this.firstShippingMethod);
                    }

                    if (
                        FleetCart.stripeEnabled &&
                        FleetCart.stripeIntegrationType === "embedded_form"
                    ) {
                        this.renderStripeElements();
                    }
                }
            });

            this._debouncedUpdateCheckout = this.debounce(() => {
                this.updateCheckoutCombined();
            }, 150);

            this._debouncedAddTaxes = this.debounce(() => {
                this.addTaxesImpl();
            }, 200);

            if (this.singleCountry) {
                this.form.billing.country = this.firstCountry;
                this.changeBillingCountry(this.firstCountry);
            }

            this.initPhoneSync();
            if (!this.form.shipping.phone && this.form.customer_phone) {
                this.form.shipping.phone = this.form.customer_phone;
            }

            this.$watch("form.billing.city", (newCity) => {
                if (newCity) {
                    this._debouncedAddTaxes();
                }
            });

            this.$watch("form.shipping.city", (newCity) => {
                if (newCity) {
                    this._debouncedAddTaxes();
                }
            });

            this.$watch("form.billing.city_id", (newCityId) => {
                this.changeBillingCityId(newCityId);
                if (newCityId) {
                    this._debouncedAddTaxes();
                }
            });

            this.$watch("form.shipping.city_id", (newCityId) => {
                this.changeShippingCityId(newCityId);
                if (newCityId) {
                    this._debouncedAddTaxes();
                }
            });

            this.$watch("form.billing.zip", (newZip) => {
                if (newZip) {
                    this._debouncedAddTaxes();
                }
            });

            this.$watch("form.shipping.zip", (newZip) => {
                if (newZip) {
                    this._debouncedAddTaxes();
                }
            });

            this.$watch("form.billing.state", (newState) => {
                if (newState) {
                    this._debouncedAddTaxes();
                }
            });

            this.$watch("form.shipping.state", (newState) => {
                if (newState) {
                    this._debouncedAddTaxes();
                }
            });

            this.$watch("form.ship_to_a_different_address", (newValue) => {
                if (!newValue) {
                    const s = this.form.shipping || {};
                    const b = this.form.billing || {};
                    this.form.billing = { ...b, ...s };
                    if (this.form.shippingAddressId) {
                        this.form.billingAddressId = this.form.shippingAddressId;
                    }
                }

                this._debouncedAddTaxes();
            });

            this.$watch("form.terms_and_conditions", () => {
                this.errors.clear("terms_and_conditions");
            });

            this.$watch("form.payment_method", (newPaymentMethod) => {
                if (newPaymentMethod === "paypal") {
                    this.$nextTick(this.renderPayPalButton());
                }
                if (newPaymentMethod) {
                    this.lastPaymentMethod = newPaymentMethod;
                }
                if (this._debouncedUpdateCheckout) this._debouncedUpdateCheckout();
            });

            // Watch customer info fields to save to abandoned cart
            this.$watch("form.customer_email", () => {
                if (this._debouncedUpdateCheckout) this._debouncedUpdateCheckout();
            });

            this.$watch("form.customer_phone", () => {
                if (this._debouncedUpdateCheckout) this._debouncedUpdateCheckout();
            });

            this.$watch("form.shipping.first_name", () => {
                if (this._debouncedUpdateCheckout) this._debouncedUpdateCheckout();
            });

            this.$watch("form.shipping.last_name", () => {
                if (this._debouncedUpdateCheckout) this._debouncedUpdateCheckout();
            });

            const defaultShippingId = this.defaultAddress.default_shipping_address_id || this.defaultAddress.address_id;
            const defaultBillingId = this.defaultAddress.default_billing_address_id || this.defaultAddress.address_id;

            if (defaultShippingId || defaultBillingId) {
                if (defaultShippingId) {
                    this.form.shippingAddressId = defaultShippingId;
                }
                if (defaultBillingId) {
                    this.form.billingAddressId = defaultBillingId;
                }

                this.mergeSavedBillingAddress();
                this.mergeSavedShippingAddress();
            }

            if (!this.hasShippingAddress) {
                this.form.newShippingAddress = true;
            }

            if (!this.hasBillingAddress) {
                this.form.newBillingAddress = true;
            }

            if (this.singleCountry) {
                this.changeBillingCountry(this.firstCountry);
                this.changeShippingCountry(this.firstCountry);
            }

            this.form.ship_to_a_different_address = false;

            if (!this.form.ship_to_a_different_address) {
                const s = this.form.shipping || {};
                const b = this.form.billing || {};
                this.form.billing = { ...b, ...s };
                if (this.form.shippingAddressId) {
                    this.form.billingAddressId = this.form.shippingAddressId;
                }
            }

            this.setTabReminder();
            this.initPhoneSync();
        },

        setTabReminder() {
            const originalTitle = document.title;
            let timeoutId;

            document.addEventListener("visibilitychange", function () {
                if (document.hidden) {
                    timeoutId = setTimeout(() => {
                        document.title = trans(
                            "storefront::checkout.remember_about_your_order"
                        );
                    }, 1000);
                } else {
                    clearTimeout(timeoutId);

                    document.title = originalTitle;
                }
            });
        },

        hideSkeleton() {
            const selectors = [
                ".cart-items-skeleton",
                ".order-summary-list-skeleton",
                ".order-summary-total-skeleton",
            ];

            selectors.forEach((selector) => {
                const element = document.querySelector(selector);

                if (!element) {
                    return;
                }

                if (typeof element.remove === "function") {
                    element.remove();
                } else if (element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            });
        },

        changeBillingAddress(address) {
            if (
                this.form.newBillingAddress ||
                this.form.billingAddressId === address.id
            ) {
                return;
            }

            this.form.billingAddressId = address.id;

            this.mergeSavedBillingAddress();


        },

        addNewBillingAddress() {
            this.resetAddressErrors("billing");

            this.form.billing = {};
            this.form.newBillingAddress = !this.form.newBillingAddress;

            if (!this.form.newBillingAddress) {
                this.mergeSavedBillingAddress();
            } else {
                if (this.singleCountry) {
                    this.form.billing.country = this.firstCountry;
                    this.changeBillingCountry(this.firstCountry);
                }
            }
        },

        changeShippingAddress(address) {
            if (
                this.form.newShippingAddress ||
                this.form.shippingAddressId === address.id
            ) {
                return;
            }

            this.form.shippingAddressId = address.id;

            this.mergeSavedShippingAddress();

            if (!this.form.ship_to_a_different_address) {
                this.form.billingAddressId = address.id;
                this.mergeSavedBillingAddress();
                const s = this.form.shipping || {};
                this.form.billing = { ...s };
            }
        },

        addNewShippingAddress() {
            this.resetAddressErrors("shipping");

            this.form.shipping = {};
            this.form.newShippingAddress = !this.form.newShippingAddress;

            if (!this.form.newShippingAddress) {
                this.mergeSavedShippingAddress();
            } else {
                if (this.singleCountry) {
                    this.form.shipping.country = this.firstCountry;
                    this.changeShippingCountry(this.firstCountry);
                }
            }
        },

        // Reset address errors based on address type
        resetAddressErrors(addressType) {
            Object.keys(this.errors.errors).map((key) => {
                key.indexOf(addressType) !== -1 && this.errors.clear(key);
            });
        },

        mergeSavedBillingAddress() {
            this.resetAddressErrors("billing");

            if (!this.form.newBillingAddress && this.form.billingAddressId) {
                const addr = this.addresses[this.form.billingAddressId] || {};
                this.form.billing = { ...addr };
                this.form.invoice = {
                    title: addr.invoice_title || addr.company_name || "",
                    tax_office: addr.invoice_tax_office || addr.tax_office || "",
                    tax_number: addr.invoice_tax_number || addr.tax_number || "",
                };
                if (!this.form.billing.billing_email) {
                    this.form.billing.billing_email = addr.billing_email || "";
                }
            }
        },

        mergeSavedShippingAddress() {
            this.resetAddressErrors("shipping");

            if (!this.form.newShippingAddress && this.form.shippingAddressId) {
                const prevPhone = this.form.shipping?.phone || "";
                const addr = this.addresses[this.form.shippingAddressId] || {};
                this.form.shipping = { ...addr };
                if (!this.form.shipping.phone) {
                    this.form.shipping.phone = prevPhone || this.form.customer_phone || "";
                }
            }
        },

        // Keep customer_phone aligned with billing phone for payment gateways
        // and legacy validation expectations.
        initPhoneSync() {
            this.$watch("form.shipping.phone", (val) => {
                if (val) {
                    this.form.customer_phone = val;
                }
            });
        },

        changeBillingCity(city) {
            this.form.billing.city = city;

            if (this.form.billing.country === "TR" && this.districts.billing.length) {
                const district = ILCELER.find((d) => String(d.ilce_adi) === String(city));
                if (district) {
                    const key = this.normalizeTR(district.sehir_adi);
                    const code = Object.keys(this.states.billing).find((c) => this.normalizeTR(this.states.billing[c]) === key);
                    if (code) {
                        this.form.billing.state = code;
                    }
                }
            }
        },

        changeBillingCityId(provinceValue) {
            this.form.billing.city_id = provinceValue;
            this.currentProvinceTRBilling = provinceValue || null;
            let cityName = null;
            const match = SEHIRLER.find((p) => String(p.sehir_id) === String(provinceValue)) || SEHIRLER.find((p) => String(p.sehir_adi) === String(provinceValue));
            cityName = match ? match.sehir_adi : (typeof provinceValue === 'string' ? provinceValue : null);
            this.form.billing.city = cityName || '';
            this.form.billing.district_id = null;
            if (!provinceValue) {
                this.billingDistricts = [];
                return;
            }
            const districtsForProvince = ILCELER.filter((d) => {
                if (d.sehir_id !== undefined && d.sehir_id !== null) {
                    return String(d.sehir_id) === String(provinceValue);
                }
                const keySel = this.normalizeTR(provinceValue);
                return this.normalizeTR(d.sehir_adi) === keySel;
            }).map((d) => ({ id: d.ilce_id ?? d.ilce_adi, name: d.ilce_adi ?? String(d) }));
            this.billingDistricts = districtsForProvince;
        },

        changeShippingCity(city) {
            this.form.shipping.city = city;

            if (this.form.shipping.country === "TR" && this.districts.shipping.length) {
                const district = ILCELER.find((d) => String(d.ilce_adi) === String(city));
                if (district) {
                    const key = this.normalizeTR(district.sehir_adi);
                    const code = Object.keys(this.states.shipping).find((c) => this.normalizeTR(this.states.shipping[c]) === key);
                    if (code) {
                        this.form.shipping.state = code;
                    }
                }
            }
        },

        changeShippingCityId(provinceValue) {
            this.form.shipping.city_id = provinceValue;
            this.currentProvinceTRShipping = provinceValue || null;
            let cityName = null;
            const match = SEHIRLER.find((p) => String(p.sehir_id) === String(provinceValue)) || SEHIRLER.find((p) => String(p.sehir_adi) === String(provinceValue));
            cityName = match ? match.sehir_adi : (typeof provinceValue === 'string' ? provinceValue : null);
            this.form.shipping.city = cityName || '';
            this.form.shipping.district_id = null;
            if (!provinceValue) {
                this.shippingDistricts = [];
                return;
            }
            const districtsForProvince = ILCELER.filter((d) => {
                if (d.sehir_id !== undefined && d.sehir_id !== null) {
                    return String(d.sehir_id) === String(provinceValue);
                }
                const keySel = this.normalizeTR(provinceValue);
                return this.normalizeTR(d.sehir_adi) === keySel;
            }).map((d) => ({ id: d.ilce_id ?? d.ilce_adi, name: d.ilce_adi ?? String(d) }));
            this.shippingDistricts = districtsForProvince;
        },
        changeBillingDistrictId(districtId) {
            this.form.billing.district_id = districtId;
            const d = (this.billingDistricts || []).find((x) => String(x.id) === String(districtId));
            this.form.billing.state = d ? d.name : '';
        },
        changeShippingDistrictId(districtId) {
            this.form.shipping.district_id = districtId;
            const d = (this.shippingDistricts || []).find((x) => String(x.id) === String(districtId));
            this.form.shipping.state = d ? d.name : '';
        },


        changeBillingZip(zip) {
            this.form.billing.zip = zip;
        },

        changeShippingZip(zip) {
            this.form.shipping.zip = zip;
        },

        changeBillingCountry(country) {
            this.form.billing.country = country;

            if (country === "") {
                this.form.billing.state = "";
                this.states.billing = {};
                this.districts.billing = [];

                return;
            }

            this.fetchStates(country, (response) => {
                this.states.billing = response.data;
                this.form.billing.state = this.form.billing.state || "";

                // TR için districts listesi city_id seçimine göre güncellenecek; burada state ile işlem yapılmıyor
            });

            if (country === "TR") {
                this.provincesTR = SEHIRLER;
                const selectedProvince = this.form.billing.city_id || null;
                this.currentProvinceTRBilling = selectedProvince;
                if (selectedProvince) {
                    const key = this.normalizeTR(selectedProvince);
                    const districtsForProvince = ILCELER
                        .filter((d) => this.normalizeTR(d.sehir_adi) === key);
                    this.districts.billing = districtsForProvince;
                } else {
                    this.districts.billing = [];
                }
            } else {
                this.districts.billing = [];
                this.currentProvinceTRBilling = null;
            }
        },

        changeShippingCountry(country) {
            this.form.shipping.country = country;

            if (country === "") {
                this.form.shipping.state = "";
                this.states.shipping = {};
                this.districts.shipping = [];

                return;
            }

            this.fetchStates(country, (response) => {
                this.states.shipping = response.data;
                this.form.shipping.state = this.form.shipping.state || "";

                // TR için districts listesi city_id seçimine göre güncellenecek; burada state ile işlem yapılmıyor
            });

            if (country === "TR") {
                this.provincesTR = SEHIRLER;
                const selectedProvince = this.form.shipping.city_id || null;
                this.currentProvinceTRShipping = selectedProvince;
                if (selectedProvince) {
                    const key = this.normalizeTR(selectedProvince);
                    const districtsForProvince = ILCELER
                        .filter((d) => this.normalizeTR(d.sehir_adi) === key);
                    this.districts.shipping = districtsForProvince;
                    if (!this.form.ship_to_a_different_address && this.form.billing?.district_id) {
                        const bDistrict = this.form.billing.district_id;
                        if (Array.isArray(this.districts.shipping) && this.districts.shipping.includes(bDistrict)) {
                            this.form.shipping.district_id = bDistrict;
                        }
                    }
                } else {
                    this.districts.shipping = [];
                }
            } else {
                this.districts.shipping = [];
                this.currentProvinceTRShipping = null;
            }
        },

        fetchStates(country, callback) {
            axios.get(`/countries/${country}/states`).then(callback);
        },

        changeBillingState(state) {
            this.form.billing.state = state;

            if (this.form.billing.country === "TR") {
                const provinceName = this.states.billing[state];
                this.currentProvinceTRBilling = provinceName || null;
                const key = this.normalizeTR(provinceName);
                const districtsForProvince = ILCELER
                    .filter((d) => this.normalizeTR(d.sehir_adi) === key)
                    .map((d) => d.ilce_adi);
                this.districts.billing = districtsForProvince;
                this.form.billing.city = "";
            }
        },

        changeShippingState(state) {
            this.form.shipping.state = state;

            if (this.form.shipping.country === "TR") {
                const provinceName = this.states.shipping[state];
                this.currentProvinceTRShipping = provinceName || null;
                const key = this.normalizeTR(provinceName);
                const districtsForProvince = ILCELER
                    .filter((d) => this.normalizeTR(d.sehir_adi) === key)
                    .map((d) => d.ilce_adi);
                this.districts.shipping = districtsForProvince;
                this.form.shipping.city = "";
            }
        },

        changePaymentMethod(paymentMethod) {
            this.form.payment_method = paymentMethod;
        },

        changeShippingMethod(shippingMethodName) {
            this.form.shipping_method = shippingMethodName;
        },

        async updateShippingMethod(shippingMethodName) {
            if (!shippingMethodName) {
                return;
            }

            this.changeShippingMethod(shippingMethodName);
            this.selectedShippingMethod = shippingMethodName;
            if (this._debouncedUpdateCheckout) this._debouncedUpdateCheckout();
        },

        async updateCheckoutCombined() {
            const pm = this.form.payment_method || this.lastPaymentMethod || this.firstPaymentMethod;
            const sm = this.selectedShippingMethod || this.cart.shippingMethodName || this.firstShippingMethod;
            const started = performance.now();
            try {
                if (this.updateController) {
                    this.updateController.abort();
                }
                this.updateController = new AbortController();
                const { data } = await axios.post('/checkout/update', {
                    payment_method: pm,
                    shipping_method: sm,
                    billing: this.form.billing || {},
                    customer_email: this.form.customer_email,
                    customer_phone: this.form.customer_phone,
                }, { signal: this.updateController.signal });
                if (data && data.cart) {
                    this.$store.cart.updateCart(data.cart);
                }
                if (data && data.payment_methods) {
                    const mapped = {};
                    (Array.isArray(data.payment_methods) ? data.payment_methods : Object.values(data.payment_methods)).forEach((pm) => {
                        mapped[pm.code] = {
                            label: pm.label,
                            description: pm.description,
                            instructions: pm.instructions,
                        };
                    });
                    this.gateways = mapped;
                }
            } catch (error) {
                if (error?.response?.data?.message) {
                    notify(error.response.data.message);
                }
            }
        },

        debounce(fn, wait) {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        },

        async addTaxesImpl() {
            try {
                const response = await axios.post("/cart/taxes", {
                    ...this.form,
                    shipping_method: this.form.shipping_method || this.selectedShippingMethod || this.firstShippingMethod,
                });
                this.$store.cart.updateCart(response.data);
            } catch (error) {
                if (error?.response?.data?.message) {
                    notify(error.response.data.message);
                }
            }
        },

        applyCoupon(directCode = null) {
            const code = directCode || this.couponCode;
            if (!code) {
                return;
            }

            this.applyingCoupon = true;
            this.couponError = null;

            axios
                .post("/cart/coupon", { coupon: code })
                .then((response) => {
                    this.couponCode = null;
                    this.couponError = null;
                    this.showCouponList = false;

                    this.$store.cart.updateCart(response.data);
                })
                .catch((error) => {
                    this.couponError = error.response.data.message;
                })
                .finally(() => {
                    this.applyingCoupon = false;
                });
        },

        applyAvailableCoupon(couponCode) {
            this.applyCoupon(couponCode);
        },



        removeCoupon() {
            axios
                .delete("/cart/coupon")
                .then(() => {
                    this.updateShippingMethod(this.form.shipping_method);
                })
                .catch((error) => {
                    notify(error.response.data.message);
                });
        },

        placeOrder() {
            if (!this.form.terms_and_conditions || this.placingOrder) {
                return;
            }

            if (!this.form.customer_phone) {
                this.form.customer_phone = this.form.shipping.phone || this.form.billing.phone || '';
            }

            if (!this.form.payment_method) {
                this.changePaymentMethod(this.firstPaymentMethod);
            }

            if (!this.form.shipping_method) {
                this.changeShippingMethod(this.firstShippingMethod);
            }

            if (!this.form.ship_to_a_different_address) {
                const s = this.form.shipping || {};
                const b = this.form.billing || {};
                this.form.billing = { ...b, ...s };
                if (!this.form.billing.phone) {
                    this.form.billing.phone = this.form.customer_phone || this.form.shipping.phone || '';
                }
                if (!this.form.customer_phone) {
                    this.form.customer_phone = this.form.shipping.phone || this.form.billing.phone || '';
                }
            }

            this.placingOrder = true;

            axios
                .post("/checkout", {
                    ...this.form,
                    ship_to_a_different_address:
                        +this.form.ship_to_a_different_address,
                })
                .then(({ data }) => {
                    if (data.redirectUrl) {
                        window.location.href = data.redirectUrl;
                    } else if (this.form.payment_method === "stripe") {
                        this.confirmStripePayment(data);
                    } else if (this.form.payment_method === "paytm") {
                        this.confirmPaytmPayment(data);
                    } else if (this.form.payment_method === "razorpay") {
                        this.confirmRazorpayPayment(data);
                    } else if (this.form.payment_method === "paystack") {
                        this.confirmPaystackPayment(data);
                    } else if (this.form.payment_method === "authorizenet") {
                        this.confirmAuthorizeNetPayment(data);
                    } else if (this.form.payment_method === "flutterwave") {
                        this.confirmFlutterWavePayment(data);
                    } else if (this.form.payment_method === "mercadopago") {
                        this.confirmMercadoPagoPayment(data);
                    } else if (this.form.payment_method === "payfast") {
                        this.confirmPayFastPayment(data);
                    } else {
                        this.confirmOrder(
                            data.orderId,
                            this.form.payment_method
                        );
                    }
                })
                .catch(({ response }) => {
                    this.placingOrder = false;

                    if (response.status === 422) {
                        this.errors.record(response.data.errors);
                        const keys = Object.keys(response.data.errors || {});
                        const firstKey = keys[0];
                        const firstMsg = firstKey ? (response.data.errors[firstKey] || [])[0] : null;
                        if (firstMsg) {
                            notify(firstMsg);
                        }
                    }

                    notify(response.data.message);
                });
        },

        confirmOrder(orderId, paymentMethod, params = {}) {
            axios
                .get(`/checkout/${orderId}/complete`, {
                    params: {
                        paymentMethod,
                        ...params,
                    },
                })
                .then(() => {
                    window.location.href = `/checkout/complete?orderId=${orderId}`;
                })
                .catch((error) => {
                    this.placingOrder = false;

                    this.deleteOrder(orderId);

                    notify(error.response.data.message);
                });
        },

        async deleteOrder(orderId) {
            if (!orderId) {
                return;
            }

            const response = await axios.get(
                `/checkout/${orderId}/payment-canceled`
            );

            notify(response.data.message);
        },

        renderPayPalButton() {
            let vm = this;
            let response;

            window.paypal
                .Buttons({
                    async createOrder() {
                        try {
                            response = await axios.post("/checkout", vm.form);

                            return response.data.resourceId;
                        } catch ({ response }) {
                            if (response.status === 422) {
                                vm.errors.record(response.data.errors);

                                return;
                            }

                            notify(response.data.message);
                        }
                    },
                    onApprove() {
                        vm.confirmOrder(
                            response.data.orderId,
                            "paypal",
                            response.data
                        );
                    },
                    onError() {
                        vm.deleteOrder(response.data.orderId);
                    },
                    onCancel() {
                        vm.deleteOrder(response.data.orderId);
                    },
                })
                .render("#paypal-button-container");
        },

        async renderStripeElements() {
            this.stripe = Stripe(FleetCart.stripePublishableKey, {});

            this.stripeElements = this.stripe.elements({
                mode: "payment",
                amount: Math.round(this.$store.cart.total * 100),
                currency: FleetCart.currency.toLowerCase(),
            });

            this.stripeElements.create("payment").mount("#stripe-element");
        },

        async confirmStripePayment({ client_secret, orderId, return_url }) {
            const elements = this.stripeElements;

            const { error: submitError } = await this.stripeElements.submit();

            if (submitError) {
                this.placingOrder = false;

                this.deleteOrder(orderId);

                notify(submitError.message);

                return;
            }

            const { error } = await this.stripe.confirmPayment({
                elements,
                clientSecret: client_secret,
                confirmParams: {
                    return_url,
                },
            });

            if (error) {
                this.placingOrder = false;

                this.deleteOrder(orderId);

                notify(error.message);
            }
        },

        confirmPaytmPayment({ orderId, amount, txnToken }) {
            let config = {
                root: "",
                flow: "DEFAULT",
                data: {
                    orderId: orderId,
                    token: txnToken,
                    tokenType: "TXN_TOKEN",
                    amount: amount,
                },
                merchant: {
                    name: FleetCart.storeName,
                    redirect: false,
                },
                handler: {
                    transactionStatus: (response) => {
                        if (response.STATUS === "TXN_SUCCESS") {
                            this.confirmOrder(orderId, "paytm", response);
                        } else if (response.STATUS === "TXN_FAILURE") {
                            this.placingOrder = false;

                            this.deleteOrder(orderId);
                        }

                        window.Paytm.CheckoutJS.close();
                    },
                    notifyMerchant: (eventName) => {
                        if (eventName === "APP_CLOSED") {
                            this.placingOrder = false;

                            this.deleteOrder(orderId);
                        }
                    },
                },
            };

            window.Paytm.CheckoutJS.init(config)
                .then(() => {
                    window.Paytm.CheckoutJS.invoke();
                })
                .catch(() => {
                    this.deleteOrder(orderId);
                });
        },

        confirmRazorpayPayment(razorpayOrder) {
            this.placingOrder = false;

            let vm = this;

            new window.Razorpay({
                key: razorpayOrder.razorpayKeyId,
                name: FleetCart.storeName,
                description: trans("storefront::checkout.payment_for_order", {
                    id: razorpayOrder.receipt,
                }),
                image: FleetCart.storeLogo,
                order_id: razorpayOrder.id,
                handler(response) {
                    vm.placingOrder = true;

                    vm.confirmOrder(
                        razorpayOrder.receipt,
                        "razorpay",
                        response
                    );
                },
                modal: {
                    ondismiss() {
                        vm.deleteOrder(razorpayOrder.receipt);
                    },
                },
                prefill: {
                    name: `${vm.form.billing.first_name} ${vm.form.billing.last_name}`,
                    email: vm.form.customer_email,
                    contact: vm.form.customer_phone,
                },
            }).open();
        },

        confirmPaystackPayment({
            key,
            email,
            amount,
            ref,
            currency,
            order_id,
        }) {
            let vm = this;

            PaystackPop.setup({
                key,
                email,
                amount,
                ref,
                currency,
                onClose() {
                    vm.placingOrder = false;

                    vm.deleteOrder(order_id);
                },
                callback(response) {
                    vm.placingOrder = false;

                    vm.confirmOrder(order_id, "paystack", response);
                },
                onBankTransferConfirmationPending(response) {
                    vm.placingOrder = false;

                    vm.confirmOrder(order_id, "paystack", response);
                },
            }).openIframe();
        },

        confirmAuthorizeNetPayment({ token }) {
            this.authorizeNetToken = token;

            this.$nextTick(() => {
                this.$refs.authorizeNetForm.submit();

                this.authorizeNetToken = null;
            });
        },

        confirmFlutterWavePayment({
            public_key,
            tx_ref,
            order_id,
            amount,
            currency,
            payment_options,
            redirect_url,
        }) {
            let vm = this;

            FlutterwaveCheckout({
                public_key,
                tx_ref,
                amount,
                currency,
                payment_options: payment_options.join(", "),
                redirect_url,
                customer: {
                    email: this.form.customer_email,
                    phone_number: this.form.customer_phone,
                    name: this.form.billing.full_name,
                },
                customizations: {
                    title: FleetCart.storeName,
                    logo: FleetCart.storeLogo,
                },
                onclose(incomplete) {
                    vm.placingOrder = false;

                    if (incomplete) {
                        vm.deleteOrder(order_id);
                    }
                },
            });
        },

        confirmMercadoPagoPayment(mercadoPagoOrder) {
            this.placingOrder = false;

            const SUPPORTED_LOCALES = {
                en_US: "en-US",
                es_AR: "es-AR",
                es_CL: "es-CL",
                es_CO: "es-CO",
                es_MX: "es-MX",
                es_VE: "es-VE",
                es_UY: "es-UY",
                es_PE: "es-PE",
                pt_BR: "pt-BR",
            };

            const mercadoPago = new MercadoPago(mercadoPagoOrder.publicKey, {
                locale:
                    SUPPORTED_LOCALES[mercadoPagoOrder.currentLocale] ||
                    "en-US",
            });

            mercadoPago.checkout({
                preference: {
                    id: mercadoPagoOrder.preferenceId,
                },
                autoOpen: true,
            });
        },

        confirmPayFastPayment(payFastOrder) {
            this.payFastFormFields = payFastOrder.formFields;

            this.$nextTick(() => {
                this.$refs.payFastForm.submit();
            });
        },
    })
);
