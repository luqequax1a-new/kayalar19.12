<div class="shipping-details">
    <div class="row">
        <div class="col-md-18">
            <div class="ship-to-different-address-form">
                <h4 class="section-title">{{ trans('storefront::checkout.shipping_details') }}</h4>

                <template x-if="hasShippingAddress">
                    <div class="address-card-wrap">
                        <div class="row">
                            <template x-for="address in shippingAddresses" :key="address.id">
                                <div class="col d-flex">
                                    <address
                                        class="address-card"
                                        :class="{
                                            active: form.shippingAddressId === address.id && !form.newShippingAddress,
                                            'cursor-default': form.newShippingAddress
                                        }"
                                        @click="changeShippingAddress(address)"
                                    >
                                        <svg class="address-card-selected-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM16.78 9.7L11.11 15.37C10.97 15.51 10.78 15.59 10.58 15.59C10.38 15.59 10.19 15.51 10.05 15.37L7.22 12.54C6.93 12.25 6.93 11.77 7.22 11.48C7.51 11.19 7.99 11.19 8.28 11.48L10.58 13.78L15.72 8.64C16.01 8.35 16.49 8.35 16.78 8.64C17.07 8.93 17.07 9.4 16.78 9.7Z" fill="#292D32"/>
                                        </svg>  
                                        
                                        <template x-if="(defaultAddress.default_shipping_address_id || defaultAddress.address_id) === address.id">
                                            <span class="badge">
                                                {{ trans('storefront::checkout.default') }}
                                            </span>
                                        </template>
                                        
                                        <div class="address-card-data">
                                            <span x-text="address.full_name"></span>
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

                        <template x-if="form.ship_to_a_different_address && !form.newShippingAddress && !form.shippingAddressId">
                            <span class="error-message">
                                {{ trans('storefront::checkout.you_must_select_an_address') }}
                            </span>
                        </template>
                    </div>
                </template>

                <div class="add-new-address-wrap">
                    <template x-if="hasAddress">
                        <button
                            type="button"
                            class="btn btn-add-new-address"
                            @click="addNewShippingAddress"
                        >
                            <span x-text="form.newShippingAddress ? '-' : '+'"></span>
                            
                            {{ trans('storefront::checkout.add_new_address') }}
                        </button>
                    </template>

                    <div class="add-new-address-form" x-show="!hasAddress || form.newShippingAddress">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="shipping-first-name">
                                        {{ trans('storefront::checkout.first_name') }}<span>*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="shipping[first_name]"
                                        id="shipping-first-name"
                                        class="form-control"
                                        x-model="form.shipping.first_name"
                                    >

                                    <template x-if="errors.has('shipping.first_name')">
                                        <span class="error-message" x-text="errors.get('shipping.first_name')"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="shipping-last-name">
                                        {{ trans('storefront::checkout.last_name') }}<span>*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="shipping[last_name]"
                                        id="shipping-last-name"
                                        class="form-control"
                                        x-model="form.shipping.last_name"
                                    >

                                    <template x-if="errors.has('shipping.last_name')">
                                        <span class="error-message" x-text="errors.get('shipping.last_name')"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="col-md-18">
                            <div class="form-group">
                                <label for="shipping-address-1">
                                    {{ trans('storefront::checkout.street_address') }}<span>*</span>
                                </label>

                                    <input
                                        type="text"
                                        name="shipping[address_line]"
                                        id="shipping-address-line"
                                        class="form-control"
                                        placeholder="{{ trans('storefront::checkout.street_address') }}"
                                        x-model="form.shipping.address_line"
                                    >

                                    <template x-if="errors.has('shipping.address_line')">
                                        <span class="error-message" x-text="errors.get('shipping.address_line')"></span>
                                    </template>
                                </div>
                            </div>

                            

                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="shipping-city-id">{{ trans('storefront::checkout.state') }}<span>*</span></label>

                                    <select
                                        name="shipping[city_id]"
                                        id="shipping-city-id"
                                        class="form-control arrow-black"
                                        x-model="form.shipping.city_id"
                                        @change="changeShippingCityId($event.target.value)"
                                    >
                                        <option value="">{{ trans('storefront::checkout.please_select') }}</option>

                                        <template x-for="p in provincesTR" :key="p.sehir_id || p.sehir_adi">
                                            <option :value="p.sehir_id ?? p.sehir_adi" x-text="p.sehir_adi"></option>
                                        </template>
                                    </select>

                                    <template x-if="errors.has('shipping.city_id')">
                                        <span class="error-message" x-text="errors.get('shipping.city_id')"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="shipping-district-id">{{ trans('storefront::checkout.city') }}<span>*</span></label>

                                    <select
                                        name="shipping[district_id]"
                                        id="shipping-district-id"
                                        class="form-control arrow-black"
                                        x-model="form.shipping.district_id"
                                        @change="changeShippingDistrictId($event.target.value)"
                                    >
                                        <option value="">{{ trans('storefront::checkout.please_select') }}</option>

                                        <template x-for="d in shippingDistricts" :key="d.id">
                                            <option :value="d.id" x-text="d.name"></option>
                                        </template>
                                    </select>

                                    <template x-if="errors.has('shipping.district_id')">
                                        <span class="error-message" x-text="errors.get('shipping.district_id')"></span>
                                    </template>
                                </div>
                            </div>

                            

                            <div class="col-md-9" x-show="!hasAddress || form.newShippingAddress">
                                <div class="form-group">
                                    <label for="shipping-phone">{{ trans('storefront::checkout.phone') }}<span>*</span></label>

                                    <div class="input-group">
                                        <span class="input-group-text">{{ trans('storefront::checkout.phone_prefix_tr') }}</span>
                                        <input
                                            type="tel"
                                            name="shipping[phone]"
                                            id="shipping-phone"
                                            class="form-control"
                                            placeholder="{{ trans('storefront::checkout.phone_placeholder_tr') }}"
                                            inputmode="numeric"
                                            pattern="^[1-9][0-9]{9}$"
                                            x-model="form.shipping.phone"
                                        >
                                    </div>
                                </div>
                            </div>

                            
                        </div>
                    </div>
                    <div class="ship-to-different-address-label">
                        <div class="ship-toggle-card d-flex align-items-center justify-content-between">
                            <label for="ship-to-different-address" class="mb-0 cursor-pointer">
                                {{ trans('storefront::checkout.ship_to_different_address') }}
                            </label>
                            
                            <label class="toggle-switch">
                                <input 
                                    type="checkbox" 
                                    id="ship-to-different-address" 
                                    x-model="form.ship_to_a_different_address"
                                >
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
