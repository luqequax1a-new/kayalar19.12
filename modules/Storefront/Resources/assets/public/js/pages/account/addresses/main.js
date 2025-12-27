import Errors from "../../../components/Errors";
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
    "Addresses",
    ({ initialAddresses, initialDefaultAddress, countries }) => ({
        addresses: initialAddresses,
        defaultAddress: initialDefaultAddress,
        countries,
        activeType: "shipping",
        formOpen: false,
        editing: false,
        loading: false,
        form: {},
        states: {},
        districts: [],
        provincesTR: SEHIRLER,
        districtOptionsTR: [],
        errors: new Errors(),

        get firstCountry() {
            const keys = Object.keys(this.countries);
            return keys[0];
        },

        get singleCountry() {
            return Object.keys(this.countries).length === 1;
        },

        get hasAddress() {
            return Object.keys(this.addresses).length !== 0;
        },

        get defaultShippingAddressId() {
            return this.defaultAddress?.default_shipping_address_id || this.defaultAddress?.address_id || null;
        },

        get defaultBillingAddressId() {
            return this.defaultAddress?.default_billing_address_id || this.defaultAddress?.address_id || null;
        },

        get shippingAddresses() {
            return Object.values(this.addresses || {}).filter((a) => a && a.type === "shipping");
        },

        get billingAddresses() {
            return Object.values(this.addresses || {}).filter((a) => a && a.type === "billing");
        },

        get activeAddresses() {
            return this.activeType === "billing" ? this.billingAddresses : this.shippingAddresses;
        },

        init() {
            this.form.country = "TR";
            this.changeCountry("TR");

            this.$watch("form.city_id", (newCityId) => {
                this.form.city_id = newCityId || null;
                this.form.district_id = null;

                const match = SEHIRLER.find((p) => String(p.sehir_id) === String(newCityId));
                this.form.city = match ? match.sehir_adi : "";

                this.districtOptionsTR = ILCELER
                    .filter((d) => String(d.sehir_id) === String(newCityId))
                    .map((d) => ({ id: d.ilce_id ?? d.ilce_adi, name: d.ilce_adi }));

                this.form.state = "";
            });

            this.$watch("form.district_id", (newDistrictId) => {
                const opt = (this.districtOptionsTR || []).find((d) => String(d.id) === String(newDistrictId));
                this.form.state = opt ? opt.name : "";
            });

            if (!this.form.type) {
                this.form.type = this.activeType;
            }
        },

        setActiveType(type) {
            this.activeType = type === "billing" ? "billing" : "shipping";

            if (!this.editing) {
                this.form.type = this.activeType;
            }

            if (this.form.type === "billing") {
                this.form.first_name = "";
                this.form.last_name = "";
            } else {
                this.form.invoice_title = "";
                this.form.invoice_tax_number = "";
                this.form.invoice_tax_office = "";
                this.form.billing_email = "";
            }
        },

        openNewAddress(type) {
            this.formOpen = true;
            this.editing = false;
            this.resetForm();
            this.setActiveType(type);

            this.$nextTick(() => {
                const panel = this.activeType === "billing" ? this.$refs?.billingFormPanel : this.$refs?.shippingFormPanel;
                if (panel?.scrollIntoView) {
                    panel.scrollIntoView({ behavior: "smooth", block: "start" });
                }

                const inputId = this.activeType === "billing" ? "address-title-billing" : "address-title-shipping";
                const input = document.getElementById(inputId);
                if (input?.focus) {
                    input.focus({ preventScroll: true });
                }
            });
        },

        changeDefaultAddress(address, type) {
            const t = type === "billing" ? "billing" : "shipping";
            const current = t === "billing" ? this.defaultBillingAddressId : this.defaultShippingAddressId;
            if (current === address.id) return;

            if (!this.defaultAddress) this.defaultAddress = {};
            if (t === "billing") {
                this.defaultAddress.default_billing_address_id = address.id;
            } else {
                this.defaultAddress.default_shipping_address_id = address.id;
            }

            axios
                .post("/account/addresses/change-default", {
                    address_id: address.id,
                    type: t,
                })
                .then((response) => {
                    notify(response.data);
                })
                .catch((error) => {
                    notify(error.response.data.message);
                });
        },

        changeCountry(country) {
            this.form.country = country;
            this.form.state = "";
            this.form.city = "";
            this.form.city_id = null;
            this.form.district_id = null;

            this.fetchStates(country, (states) => {
                this.states = states;
                if (country !== "TR") {
                    this.districts = [];
                    this.districtOptionsTR = [];
                } else {
                    this.districts = [];
                    this.districtOptionsTR = [];
                }
            });
        },

        normalizeTR(s) {
            if (!s) return s;
            const map = { İ: 'I', ı: 'I', i: 'I', I: 'I', Ç: 'C', ç: 'c', Ş: 'S', ş: 's', Ğ: 'G', ğ: 'g', Ü: 'U', ü: 'u', Ö: 'O', ö: 'o' };
            const t = String(s)
                .replace(/[İıiIÇçŞşĞğÜüÖö]/g, (m) => map[m])
                .normalize('NFD').replace(/\p{Diacritic}/gu, '')
                .toUpperCase();
            return t;
        },

        async fetchStates(country, callback) {
            const response = await axios.get(`/countries/${country}/states`);

            if (callback) {
                callback(response.data);
            }
        },

        changeCityId(cityId) {
            this.form.city_id = cityId || null;
        },

        changeDistrictId(districtId) {
            this.form.district_id = districtId || null;
        },

        resolveProvinceIdFromName(name) {
            if (!name) return null;
            const key = this.normalizeTR(name);
            const match = SEHIRLER.find((p) => this.normalizeTR(p.sehir_adi) === key);
            return match ? (match.sehir_id ?? null) : null;
        },

        resolveDistrictIdFromName(name, cityId = null) {
            if (!name) return null;
            const key = this.normalizeTR(name);
            const list = cityId
                ? ILCELER.filter((d) => String(d.sehir_id) === String(cityId))
                : ILCELER;
            const match = list.find((d) => this.normalizeTR(d.ilce_adi) === key);
            return match ? (match.ilce_id ?? match.ilce_adi) : null;
        },

        edit(address) {
            this.formOpen = true;
            this.editing = true;

            this.activeType = address?.type === "billing" ? "billing" : "shipping";

            this.$nextTick(() => {
                this.form = { ...address };

                this.$nextTick(() => {
                    const panel = this.activeType === "billing" ? this.$refs?.billingFormPanel : this.$refs?.shippingFormPanel;
                    if (panel?.scrollIntoView) {
                        panel.scrollIntoView({ behavior: "smooth", block: "start" });
                    }

                    const inputId = this.activeType === "billing" ? "address-title-billing" : "address-title-shipping";
                    const input = document.getElementById(inputId);
                    if (input?.focus) {
                        input.focus({ preventScroll: true });
                    }
                });

                this.fetchStates(address.country, (states) => {
                    this.states = states;
                    this.form.state = "";

                    this.$nextTick(() => {
                        if (address.country === "TR") {
                            const cityId = address.city_id || this.resolveProvinceIdFromName(address.city);
                            this.form.city_id = cityId;
                            this.form.city = address.city || (SEHIRLER.find((p) => String(p.sehir_id) === String(cityId))?.sehir_adi ?? "");

                            this.districtOptionsTR = ILCELER
                                .filter((d) => String(d.sehir_id) === String(cityId))
                                .map((d) => ({ id: d.ilce_id ?? d.ilce_adi, name: d.ilce_adi }));

                            const districtId = address.district_id || this.resolveDistrictIdFromName(address.state, cityId);
                            this.form.district_id = districtId;

                            const opt = (this.districtOptionsTR || []).find((d) => String(d.id) === String(districtId));
                            this.form.state = opt ? opt.name : (address.state || "");
                        } else {
                            this.districts = [];
                            this.form.state = address.state;
                            this.form.city = address.city;
                        }
                    });
                });
            });
        },

        remove(address) {
            if (!confirm(trans("storefront::account.addresses.confirm"))) {
                return;
            }

            axios
                .delete(`/account/addresses/${address.id}`)
                .then((response) => {
                    delete this.addresses[address.id];

                    notify(response.data.message);
                })
                .catch((error) => {
                    notify(error.response.data.message);
                });
        },

        cancel() {
            this.editing = false;
            this.formOpen = false;

            this.errors.reset();
            this.resetForm();
        },

        save() {
            this.loading = true;

            this.editing ? this.update() : this.create();
        },

        update() {
            const payload = { ...this.form };

            axios
                .put(`/account/addresses/${payload.id}`, payload)
                .then(({ data }) => {
                    this.formOpen = false;
                    this.editing = false;

                    this.addresses[payload.id] = data.address;

                    this.resetForm();

                    notify.success(
                        payload.type === "billing"
                            ? trans("storefront::account.addresses.billing_address_updated")
                            : trans("storefront::account.addresses.shipping_address_updated")
                    );
                })
                .catch(({ response }) => {
                    if (response.status === 422) {
                        this.errors.record(response.data.errors);
                        return;
                    }

                    notify.error(response?.data?.message || trans('storefront::storefront.something_went_wrong'));
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        create() {
            const payload = { ...this.form };

            if (!payload.type) {
                payload.type = this.activeType;
            }

            axios
                .post('/account/addresses', payload)
                .then(({ data }) => {
                    this.formOpen = false;
                    this.addresses = { ...this.addresses, [data.address.id]: data.address };
                    this.resetForm();
                    notify.success(
                        payload.type === "billing"
                            ? trans("storefront::account.addresses.billing_address_created")
                            : trans("storefront::account.addresses.shipping_address_created")
                    );
                })
                .catch(({ response }) => {
                    if (response?.status === 422) {
                        this.errors.record(response.data.errors);
                        return;
                    }
                    notify.error(response?.data?.message || trans('storefront::storefront.something_went_wrong'));
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        resetForm() {
            this.form = { type: this.activeType, country: "TR", city_id: null, district_id: null };
        },
    })
);
