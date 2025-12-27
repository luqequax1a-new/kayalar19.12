<div class="billing-details" x-cloak x-show="form.ship_to_a_different_address">
    <h4 class="section-title">{{ trans('storefront::checkout.billing_details') }}</h4>

    <template x-if="hasBillingAddress">
        <div x-cloak class="address-card-wrap">
            <div class="row">
                <template x-for="address in billingAddresses" :key="address.id">
                    <div class="col d-flex">
                        <address
                            class="address-card"
                            :class="{
                                active: form.billingAddressId === address.id && !form.newBillingAddress,
                                'cursor-default': form.newBillingAddress
                            }"
                            @click="changeBillingAddress(address)"
                        >
                            <svg class="address-card-selected-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM16.78 9.7L11.11 15.37C10.97 15.51 10.78 15.59 10.58 15.59C10.38 15.59 10.19 15.51 10.05 15.37L7.22 12.54C6.93 12.25 6.93 11.77 7.22 11.48C7.51 11.19 7.99 11.19 8.28 11.48L10.58 13.78L15.72 8.64C16.01 8.35 16.49 8.35 16.78 8.64C17.07 8.93 17.07 9.4 16.78 9.7Z" fill="#292D32"/>
                            </svg>    

                            <template x-if="(defaultAddress.default_billing_address_id || defaultAddress.address_id) === address.id">
                                <span class="badge">
                                    {{ trans('storefront::checkout.default') }}
                                </span>
                            </template>
                            
                            <div class="address-card-data">
                                <template x-if="address.invoice_title || address.company_name">
                                    <span x-text="`${trans('storefront::checkout.label_company_name')} ${address.invoice_title || address.company_name}`"></span>
                                </template>
                                <template x-if="address.invoice_tax_number || address.tax_number">
                                    <span x-text="`${trans('storefront::checkout.label_tax_number')} ${address.invoice_tax_number || address.tax_number}`"></span>
                                </template>
                                <template x-if="address.invoice_tax_office || address.tax_office">
                                    <span x-text="`${trans('storefront::checkout.label_tax_office')} ${address.invoice_tax_office || address.tax_office}`"></span>
                                </template>

                                <span x-text="address.address_line || address.address_1"></span>
                                <template x-if="address.address_2">
                                    <span x-text="address.address_2"></span>
                                </template>

                                <span x-text="`${address.city_title ?? address.city}, ${address.state_name ?? address.state}`"></span>
                                <template x-if="address.phone">
                                    <span x-text="`${trans('storefront::checkout.label_phone_prefix_tr')} ${address.phone}`"></span>
                                </template>
                            </div>
                        </address>
                    </div>
                </template>
            </div>

            <template x-if="!form.newBillingAddress && !form.billingAddressId">
                <span class="error-message">
                    {{ trans('storefront::checkout.you_must_select_an_address') }}
                </span>
            </template>
        </div>
    </template>

    <div x-cloak class="add-new-address-wrap">
        <template x-if="hasAddress">
            <button type="button" class="btn btn-add-new-address" @click="addNewBillingAddress">
                <span x-text="form.newBillingAddress ? '-' : '+'"></span>
                
                {{ trans('storefront::checkout.add_new_address') }}
            </button>
        </template>

        <div class="add-new-address-form" x-show="!hasAddress || form.newBillingAddress">
            <div class="row">

                <!-- Name fields removed: Billing adında zorunlu değil -->

                <!-- Invoice block first -->
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-company-name">{{ trans('storefront::checkout.company_name') }}</label>

                        <input
                            type="text"
                            name="billing[company_name]"
                            id="billing-company-name"
                            class="form-control"
                            x-model="form.billing.company_name"
                        >
                        <template x-if="errors.has('billing.company_name')">
                            <span class="error-message" x-text="errors.get('billing.company_name')"></span>
                        </template>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-tax-number">{{ trans('storefront::checkout.tax_number') }}</label>

                        <input
                            type="text"
                            name="billing[tax_number]"
                            id="billing-tax-number"
                            class="form-control"
                            x-model="form.billing.tax_number"
                        >
                        <template x-if="errors.has('billing.tax_number')">
                            <span class="error-message" x-text="errors.get('billing.tax_number')"></span>
                        </template>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-tax-office">{{ trans('storefront::checkout.tax_office') }}</label>

                        <input
                            type="text"
                            name="billing[tax_office]"
                            id="billing-tax-office"
                            class="form-control"
                            x-model="form.billing.tax_office"
                        >
                        <template x-if="errors.has('billing.tax_office')">
                            <span class="error-message" x-text="errors.get('billing.tax_office')"></span>
                        </template>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-email">{{ trans('storefront::checkout.email') }}</label>

                        <input
                            type="email"
                            name="billing[billing_email]"
                            id="billing-email"
                            class="form-control"
                            x-model="form.billing.billing_email"
                        >

                        <template x-if="errors.has('billing.billing_email')">
                            <span class="error-message" x-text="errors.get('billing.billing_email')"></span>
                        </template>
                    </div>
                </div>

                <!-- Address line -->
                <div class="col-md-18">
                    <div class="form-group">
                        <label for="billing-address-line">
                            {{ trans('storefront::checkout.street_address') }}
                        </label>

                        <input
                            type="text"
                            name="billing[address_line]"
                            id="billing-address-line"
                            class="form-control"
                            placeholder="{{ trans('storefront::checkout.street_address') }}"
                            x-model="form.billing.address_line"
                        >

                        <template x-if="errors.has('billing.address_line')">
                            <span class="error-message" x-text="errors.get('billing.address_line')"></span>
                        </template>
                    </div>
                </div>

                <!-- Province -->
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-city-id">{{ trans('storefront::checkout.state') }}</label>
                            <select name="billing[city_id]" id="billing-city-id" class="form-control arrow-black" x-model="form.billing.city_id" @change="changeBillingCityId($event.target.value)">
                                <option value="">{{ trans('storefront::checkout.please_select') }}</option>
                                <template x-for="p in provincesTR" :key="p.sehir_id || p.sehir_adi">
                                    <option :value="p.sehir_id ?? p.sehir_adi" x-text="p.sehir_adi"></option>
                                </template>
                            </select>
                        <template x-if="errors.has('billing.city_id')">
                            <span class="error-message" x-text="errors.get('billing.city_id')"></span>
                        </template>
                    </div>
                </div>

                <!-- District -->
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-district-id">{{ trans('storefront::checkout.city') }}</label>
                            <select name="billing[district_id]" id="billing-district-id" class="form-control arrow-black" x-model="form.billing.district_id" @change="changeBillingDistrictId($event.target.value)">
                                <option value="">{{ trans('storefront::checkout.please_select') }}</option>
                                <template x-for="d in billingDistricts" :key="d.id">
                                    <option :value="d.id" x-text="d.name"></option>
                                </template>
                            </select>
                        <template x-if="errors.has('billing.district_id')">
                            <span class="error-message" x-text="errors.get('billing.district_id')"></span>
                        </template>
                    </div>
                </div>

                <!-- Phone -->

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-phone">{{ trans('storefront::checkout.phone') }}</label>

                        <input
                            type="text"
                            name="billing[phone]"
                            id="billing-phone"
                            class="form-control"
                            x-model="form.billing.phone"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
