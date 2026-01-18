
import sehirler from "@modules/../sehirler.json";
import ilceler from "@modules/../ilceler.json";

(function ($) {
    "use strict";

    let storeCountry = $("#store_country");
    let storeStateInputWrapper = $(".store-state.input");
    let storeStateSelectWrapper = $(".store-state.select");
    let storeStateInput = $("#store_state_input");
    let storeStateSelect = $("#store_state_select");

    let storeCityInputWrapper = $(".store-city.input");
    let storeCitySelectWrapper = $(".store-city.select");
    let storeCityInput = $("#store_city_input");
    let storeCitySelect = $("#store_city_select");

    // Case insensitive title case for TR
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

    function normalizeTR(s) {
        if (!s) return s;
        const map = { İ: 'I', ı: 'I', i: 'I', I: 'I', Ç: 'C', ç: 'c', Ş: 'S', ş: 's', Ğ: 'G', ğ: 'g', Ü: 'U', ü: 'u', Ö: 'O', ö: 'o' };
        return String(s)
            .replace(/[İıiIÇçŞşĞğÜüÖö]/g, (m) => map[m])
            .normalize('NFD').replace(/\p{Diacritic}/gu, '')
            .toUpperCase();
    }

    function getTrans(key, defaultVal) {
        if (window.trans) {
            let res = window.trans(key);
            if (res && res !== key) return res;
        }
        return defaultVal || "Select...";
    }

    function updateStateSelect(states, selectedState) {
        if ($.isEmptyObject(states)) {
            storeStateSelectWrapper.addClass("hide");
            storeStateInputWrapper.removeClass("hide");
            storeStateSelect.prop("disabled", true);
            storeStateInput.prop("disabled", false);
        } else {
            storeStateInputWrapper.addClass("hide");
            storeStateSelectWrapper.removeClass("hide");
            storeStateInput.prop("disabled", true);
            storeStateSelect.prop("disabled", false);

            storeStateSelect.empty();
            let placeholder = getTrans("admin::admin.form.please_select", "Please Select");
            storeStateSelect.append('<option value="">' + placeholder + '</option>');

            $.each(states, function (code, name) {
                let selected = code == selectedState ? "selected" : "";
                storeStateSelect.append('<option value="' + code + '" ' + selected + '>' + name + '</option>');
            });

            if (storeStateSelect[0].selectize) {
                storeStateSelect[0].selectize.destroy();
                storeStateSelect.selectize();
            }
        }
    }

    function updateCitySelect(districts, selectedCity) {
        if (!districts || districts.length === 0) {
            storeCitySelectWrapper.addClass("hide");
            storeCityInputWrapper.removeClass("hide");
            storeCitySelect.prop("disabled", true);
            storeCityInput.prop("disabled", false);
        } else {
            storeCityInputWrapper.addClass("hide");
            storeCitySelectWrapper.removeClass("hide");
            storeCityInput.prop("disabled", true);
            storeCitySelect.prop("disabled", false);

            storeCitySelect.empty();
            let placeholder = getTrans("admin::admin.form.please_select", "Please Select");
            storeCitySelect.append('<option value="">' + placeholder + '</option>');

            $.each(districts, function (idx, district) {
                let val = district.ilce_adi; // Checkout uses name or id? Checkout seems to use name for state but ID/Name mix for district. Admin usually saves Name for City.
                // Checkout: map((d) => ({ id: d.ilce_id ?? d.ilce_adi, name: d.ilce_adi ?? String(d) }))
                // Store City is usually text, so we should probably save the Name.
                let selected = val == selectedCity ? "selected" : "";
                storeCitySelect.append('<option value="' + val + '" ' + selected + '>' + val + '</option>');
            });

            if (storeCitySelect[0].selectize) {
                storeCitySelect[0].selectize.destroy();
                storeCitySelect.selectize();
            }
        }
    }

    function fetchStates(countryCode) {
        if (!countryCode) return;

        // Current values
        // Determine current state value. If we are just switching countries, it might be empty.
        // If we are loading, it might be populated.
        // We can try to take value from Input or Select.
        // Ensure we don't get 'undefined' string
        let currentState = storeStateSelect.val() || storeStateInput.val() || storeStateSelect.data('initial-value') || "";

        if (countryCode === 'TR') {
            // Use SEHIRLER
            let states = {};
            SEHIRLER.forEach(s => {
                states[s.sehir_id] = s.sehir_adi;
            });
            updateStateSelect(states, currentState);

            // Trigger state change to populate cities if state is selected
            if (currentState) {
                handleStateChange(currentState);
            }
        } else {
            // Standard AJAX
            $.ajax({
                type: "GET",
                url: window.route ? window.route("countries.states.index", countryCode) : `/countries/${countryCode}/states`,
                success: function (states) {
                    updateStateSelect(states, currentState);
                    // For non-TR, we don't have districts (cities), so show input for city
                    updateCitySelect([], null);
                }
            });
        }
    }

    function handleStateChange(stateValue) {
        let currentCountry = storeCountry.val();
        let currentCity = storeCitySelect.val() || storeCityInput.val() || storeCitySelect.data('initial-value') || "";

        if (currentCountry === 'TR' && stateValue) {
            // Filter ILCELER
            // Checkout logic:
            // const districtsForProvince = ILCELER.filter((d) => String(d.sehir_id) === String(provinceValue));
            let districts = ILCELER.filter(d => String(d.sehir_id) === String(stateValue));
            updateCitySelect(districts, currentCity);
        } else {
            // Show input if not TR or no state
            updateCitySelect([], currentCity);
        }
    }

    if (storeCountry.length > 0) {
        // Store initial values in data attributes if needed, or just grab from value
        // Blade renders value into input.

        // Initial Load
        fetchStates(storeCountry.val());

        storeCountry.on("change", function () {
            // Clear downstream selections on country change
            storeStateInput.val('');
            storeStateSelect.val('');
            if (storeStateSelect[0].selectize) storeStateSelect[0].selectize.clear();

            storeCityInput.val('');
            storeCitySelect.val('');
            if (storeCitySelect[0].selectize) storeCitySelect[0].selectize.clear();

            fetchStates(this.value);
        });

        storeStateSelect.on("change", function () {
            handleStateChange(this.value);
        });
    }

    // Generic Toggle for Settings (Google, Facebook, Payment Gateways, etc.)
    // Scans for checkboxes sticking to convention: name="{prefix}_enabled" -> id="{prefix}-fields"
    // e.g. google_login_enabled -> google-login-fields
    $('input[type=checkbox][name$="_enabled"]').on('change', function () {
        let name = $(this).attr('name');
        let prefix = name.replace('_enabled', '').replace(/_/g, '-');
        let targetId = '#' + prefix + '-fields';

        let $target = $(targetId);

        if ($target.length > 0) {
            if (this.checked) {
                $target.removeClass('hide');
            } else {
                $target.addClass('hide');
            }
        }
    });

})(jQuery);
