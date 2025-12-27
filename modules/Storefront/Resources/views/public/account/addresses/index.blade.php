@extends('storefront::public.account.layout')

@section('title', trans('storefront::account.pages.my_addresses'))

@section('account_breadcrumb')
    <li class="active">{{ trans('storefront::account.pages.my_addresses') }}</li>
@endsection

@section('panel')
    <div
        x-data="Addresses({
            initialAddresses: {{ $addresses }},
            initialDefaultAddress: {{ $defaultAddress }},
            countries: {{ json_encode($countries) }}
        })"
    >
        <div class="panel">
            <div class="panel-header">
                <h4>{{ trans('storefront::account.addresses.shipping_addresses') }}</h4>
            </div>

            <div x-cloak class="panel-body">
                <div class="my-addresses">
                    <div class="address-card-wrap">
                        <template x-if="shippingAddresses.length">
                            <div class="row">
                                <template x-for="address in shippingAddresses" :key="address.id">
                                    <div class="col-xl-6 col-lg-9 d-flex">
                                        <address
                                            class="address-card d-flex flex-column justify-content-between"
                                            :class="{ active: defaultShippingAddressId === address.id }"
                                            @click="changeDefaultAddress(address, 'shipping')"
                                        >
                                                    <svg class="address-card-selected-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM16.78 9.7L11.11 15.37C10.97 15.51 10.78 15.59 10.58 15.59C10.38 15.59 10.19 15.51 10.05 15.37L7.22 12.54C6.93 12.25 6.93 11.77 7.22 11.48C7.51 11.19 7.99 11.19 8.28 11.48L10.58 13.78L15.72 8.64C16.01 8.35 16.49 8.35 16.78 8.64C17.07 8.93 17.07 9.4 16.78 9.7Z" fill="#292D32"/>
                                                    </svg>

                                                    <template x-if="defaultShippingAddressId === address.id">
                                                        <span class="badge">
                                                            {{ trans('storefront::account.addresses.default') }}
                                                        </span>
                                                    </template>

                                                    <div class="address-card-data">
                                                        <template x-if="address.address_title">
                                                            <span x-text="address.address_title"></span>
                                                        </template>
                                                        <span x-text="address.full_name"></span>
                                                        <span x-text="address.address_1"></span>

                                                        <template x-if="address.address_2">
                                                            <span x-text="address.address_2"></span>
                                                        </template>

                                                        <span x-text="`${address.city_title}, ${address.state_name ?? address.state}`"></span>
                                                        <template x-if="address.zip">
                                                            <span x-text="address.zip"></span>
                                                        </template>
                                                        <template x-if="address.phone">
                                                            <span x-text="`${trans('storefront::account.addresses.label_phone_prefix_tr')} ${address.phone}`"></span>
                                                        </template>
                                                        <template x-if="address.invoice_title || address.company_name">
                                                            <span x-text="`${trans('storefront::account.addresses.label_company_name')} ${address.invoice_title || address.company_name}`"></span>
                                                        </template>
                                                        <template x-if="address.invoice_tax_number || address.tax_number">
                                                            <span x-text="`${trans('storefront::account.addresses.label_tax_number')} ${address.invoice_tax_number || address.tax_number}`"></span>
                                                        </template>
                                                        <template x-if="address.invoice_tax_office || address.tax_office">
                                                            <span x-text="`${trans('storefront::account.addresses.label_tax_office')} ${address.invoice_tax_office || address.tax_office}`"></span>
                                                        </template>
                                                    </div>

                                                    <div class="address-card-actions">
                                                        <button type="button" class="btn btn-edit-address" @click.stop="edit(address)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                <path d="M11 2H9C4 2 2 4 2 9V15C2 20 4 22 9 22H15C20 22 22 20 22 15V13" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M16.04 3.02001L8.16 10.9C7.86 11.2 7.56 11.79 7.5 12.22L7.07 15.23C6.91 16.32 7.68 17.08 8.77 16.93L11.78 16.5C12.2 16.44 12.79 16.14 13.1 15.84L20.98 7.96001C22.34 6.60001 22.98 5.02001 20.98 3.02001C18.98 1.02001 17.4 1.66001 16.04 3.02001Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M14.91 4.1499C15.58 6.5399 17.45 8.4099 19.85 9.0899" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>

                                                            {{ trans('storefront::account.addresses.edit') }}
                                                        </button>

                                                        <button type="button" class="btn btn-delete-address" @click.stop="remove(address)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                <path d="M21 5.97998C17.67 5.64998 14.32 5.47998 10.98 5.47998C9 5.47998 7.02 5.57998 5.04 5.77998L3 5.97998" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M8.5 4.97L8.72 3.66C8.88 2.71 9 2 10.69 2H13.31C15 2 15.13 2.75 15.28 3.67L15.5 4.97" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M18.85 9.14001L18.2 19.21C18.09 20.78 18 22 15.21 22H8.79C6 22 5.91 20.78 5.8 19.21L5.15 9.14001" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M10.33 16.5H13.66" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M9.5 12.5H14.5" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>

                                                            {{ trans('storefront::account.addresses.delete') }}
                                                        </button>
                                                    </div>
                                        </address>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="row">
                            <div class="col-18 col-lg-18">
                                <button
                                    type="button"
                                    class="btn btn-lg btn-default btn-add-new-address"
                                    @click="openNewAddress('shipping')"
                                >
                                    {{ trans('storefront::account.addresses.add_new_shipping_address') }}
                                </button>
                            </div>
                        </div>

                        <div
                            class="add-new-address-form address-form-panel"
                            x-cloak
                            x-show="formOpen && form.type === 'shipping'"
                            x-ref="shippingFormPanel"
                        >
                            <form @submit.prevent="save" @input="errors.clear($event.target.name)">
                                <input type="hidden" name="type" x-model="form.type">

                                <div class="row">
                                    <div class="col-18 col-lg-18">
                                        <div class="form-group">
                                            <label for="address-title-shipping">{{ trans('storefront::account.addresses.address_title') }}</label>

                                            <input
                                                name="address_title"
                                                type="text"
                                                id="address-title-shipping"
                                                class="form-control"
                                                placeholder="{{ trans('storefront::account.addresses.address_title_placeholder_shipping') }}"
                                                x-model="form.address_title"
                                            >

                                            <template x-if="errors.has('address_title')">
                                                <span class="error-message" x-text="errors.get('address_title')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="shipping-first-name">
                                                {{ trans('storefront::account.addresses.first_name') }}<span>*</span>
                                            </label>

                                            <input
                                                name="first_name"
                                                type="text"
                                                id="shipping-first-name"
                                                class="form-control"
                                                x-model="form.first_name"
                                            >

                                            <template x-if="errors.has('first_name')">
                                                <span class="error-message" x-text="errors.get('first_name')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="shipping-last-name">
                                                {{ trans('storefront::account.addresses.last_name') }}<span>*</span>
                                            </label>

                                            <input
                                                name="last_name"
                                                type="text"
                                                id="shipping-last-name"
                                                class="form-control"
                                                x-model="form.last_name"
                                            >

                                            <template x-if="errors.has('last_name')">
                                                <span class="error-message" x-text="errors.get('last_name')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-18">
                                        <div class="form-group">
                                            <label for="shipping-address-1">
                                                {{ trans('storefront::account.addresses.street_address') }}<span>*</span>
                                            </label>

                                            <input
                                                name="address_1"
                                                type="text"
                                                id="shipping-address-1"
                                                placeholder="{{ trans('storefront::account.addresses.address_line_1') }}"
                                                class="form-control"
                                                x-model="form.address_1"
                                            >

                                            <template x-if="errors.has('address_1')">
                                                <span class="error-message" x-text="errors.get('address_1')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="shipping-city-id">{{ trans('storefront::account.addresses.state') }}<span>*</span></label>

                                            <select
                                                name="city_id"
                                                id="shipping-city-id"
                                                class="form-control arrow-black"
                                                x-model="form.city_id"
                                                @change="changeCityId($event.target.value)"
                                            >
                                                <option value="">{{ trans('storefront::account.addresses.please_select') }}</option>
                                                <template x-for="p in provincesTR" :key="p.sehir_id">
                                                    <option :value="p.sehir_id" x-text="p.sehir_adi"></option>
                                                </template>
                                            </select>

                                            <template x-if="errors.has('city_id')">
                                                <span class="error-message" x-text="errors.get('city_id')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="shipping-district-id">{{ trans('storefront::account.addresses.city') }}<span>*</span></label>

                                            <select
                                                name="district_id"
                                                id="shipping-district-id"
                                                class="form-control arrow-black"
                                                x-model="form.district_id"
                                                @change="changeDistrictId($event.target.value)"
                                            >
                                                <option value="">{{ trans('storefront::account.addresses.please_select') }}</option>
                                                <template x-for="d in districtOptionsTR" :key="d.id">
                                                    <option :value="d.id" x-text="d.name"></option>
                                                </template>
                                            </select>

                                            <template x-if="errors.has('district_id')">
                                                <span class="error-message" x-text="errors.get('district_id')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9" x-cloak x-show="!singleCountry">
                                        <div class="form-group">
                                            <label for="shipping-country">
                                                {{ trans('storefront::account.addresses.country') }}<span>*</span>
                                            </label>

                                            <select
                                                :value="form.country"
                                                name="country"
                                                id="shipping-country"
                                                class="form-control arrow-black"
                                                @change="changeCountry($event.target.value)"
                                            >
                                                <template x-for="(name, code) in countries">
                                                    <option :value="code" x-text="name"></option>
                                                </template>
                                            </select>

                                            <template x-if="errors.has('country')">
                                                <span class="error-message" x-text="errors.get('country')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="shipping-phone">{{ trans('storefront::account.addresses.phone') }}<span>*</span></label>

                                            <div class="input-group">
                                                <span class="input-group-text">{{ trans('storefront::account.addresses.phone_prefix_tr') }}</span>
                                                <input
                                                    type="tel"
                                                    name="phone"
                                                    id="shipping-phone"
                                                    class="form-control"
                                                    placeholder="{{ trans('storefront::account.addresses.phone_placeholder_tr') }}"
                                                    inputmode="numeric"
                                                    pattern="^[1-9][0-9]{9}$"
                                                    x-model="form.phone"
                                                >
                                            </div>

                                            <template x-if="errors.has('phone')">
                                                <span class="error-message" x-text="errors.get('phone')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-18">
                                        <button
                                            type="button"
                                            class="btn btn-lg btn-default btn-cancel"
                                            @click="cancel"
                                        >
                                            {{ trans('storefront::account.addresses.cancel') }}
                                        </button>

                                        <button
                                            type="submit"
                                            class="btn btn-lg btn-primary btn-save-address"
                                            :class="{ 'btn-loading': loading }"
                                        >
                                            {{ trans('storefront::account.addresses.save_address') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-top: 24px;">
            <div class="panel-header">
                <h4>{{ trans('storefront::account.addresses.billing_addresses') }}</h4>
            </div>

            <div x-cloak class="panel-body">
                <div class="my-addresses">
                    <div class="address-card-wrap">
                        <template x-if="billingAddresses.length">
                            <div class="row">
                                <template x-for="address in billingAddresses" :key="address.id">
                                    <div class="col-xl-6 col-lg-9 d-flex">
                                        <address
                                            class="address-card d-flex flex-column justify-content-between"
                                            :class="{ active: defaultBillingAddressId === address.id }"
                                            @click="changeDefaultAddress(address, 'billing')"
                                        >
                                                    <svg class="address-card-selected-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM16.78 9.7L11.11 15.37C10.97 15.51 10.78 15.59 10.58 15.59C10.38 15.59 10.19 15.51 10.05 15.37L7.22 12.54C6.93 12.25 6.93 11.77 7.22 11.48C7.51 11.19 7.99 11.19 8.28 11.48L10.58 13.78L15.72 8.64C16.01 8.35 16.49 8.35 16.78 8.64C17.07 8.93 17.07 9.4 16.78 9.7Z" fill="#292D32"/>
                                                    </svg>

                                                    <template x-if="defaultBillingAddressId === address.id">
                                                        <span class="badge">
                                                            {{ trans('storefront::account.addresses.default') }}
                                                        </span>
                                                    </template>

                                                    <div class="address-card-data">
                                                        <template x-if="address.address_title">
                                                            <span x-text="address.address_title"></span>
                                                        </template>
                                                        <template x-if="address.invoice_title || address.company_name">
                                                            <span x-text="`${trans('storefront::account.addresses.label_company_name')} ${address.invoice_title || address.company_name}`"></span>
                                                        </template>
                                                        <template x-if="address.invoice_tax_number || address.tax_number">
                                                            <span x-text="`${trans('storefront::account.addresses.label_tax_number')} ${address.invoice_tax_number || address.tax_number}`"></span>
                                                        </template>
                                                        <template x-if="address.invoice_tax_office || address.tax_office">
                                                            <span x-text="`${trans('storefront::account.addresses.label_tax_office')} ${address.invoice_tax_office || address.tax_office}`"></span>
                                                        </template>
                                                        <span x-text="address.address_1"></span>
                                                        <span x-text="`${address.city_title}, ${address.district_title ?? address.state_name ?? address.state}`"></span>
                                                        <template x-if="address.phone">
                                                            <span x-text="`${trans('storefront::account.addresses.label_phone_prefix_tr')} ${address.phone}`"></span>
                                                        </template>
                                                    </div>

                                                    <div class="address-card-actions">
                                                        <button type="button" class="btn btn-edit-address" @click.stop="edit(address)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                <path d="M11 2H9C4 2 2 4 2 9V15C2 20 4 22 9 22H15C20 22 22 20 22 15V13" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M16.04 3.02001L8.16 10.9C7.86 11.2 7.56 11.79 7.5 12.22L7.07 15.23C6.91 16.32 7.68 17.08 8.77 16.93L11.78 16.5C12.2 16.44 12.79 16.14 13.1 15.84L20.98 7.96001C22.34 6.60001 22.98 5.02001 20.98 3.02001C18.98 1.02001 17.4 1.66001 16.04 3.02001Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M14.91 4.1499C15.58 6.5399 17.45 8.4099 19.85 9.0899" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>

                                                            {{ trans('storefront::account.addresses.edit') }}
                                                        </button>

                                                        <button type="button" class="btn btn-delete-address" @click.stop="remove(address)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                <path d="M21 5.97998C17.67 5.64998 14.32 5.47998 10.98 5.47998C9 5.47998 7.02 5.57998 5.04 5.77998L3 5.97998" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M8.5 4.97L8.72 3.66C8.88 2.71 9 2 10.69 2H13.31C15 2 15.13 2.75 15.28 3.67L15.5 4.97" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M18.85 9.14001L18.2 19.21C18.09 20.78 18 22 15.21 22H8.79C6 22 5.91 20.78 5.8 19.21L5.15 9.14001" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M10.33 16.5H13.66" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path d="M9.5 12.5H14.5" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>

                                                            {{ trans('storefront::account.addresses.delete') }}
                                                        </button>
                                                    </div>
                                        </address>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="row">
                            <div class="col-18 col-lg-18">
                                <button
                                    type="button"
                                    class="btn btn-lg btn-default btn-add-new-address"
                                    @click="openNewAddress('billing')"
                                >
                                    {{ trans('storefront::account.addresses.add_new_billing_address') }}
                                </button>
                            </div>
                        </div>

                        <div
                            class="add-new-address-form address-form-panel"
                            x-cloak
                            x-show="formOpen && form.type === 'billing'"
                            x-ref="billingFormPanel"
                        >
                            <form @submit.prevent="save" @input="errors.clear($event.target.name)">
                                <input type="hidden" name="type" x-model="form.type">

                                <div class="row">
                                    <div class="col-18 col-lg-18">
                                        <div class="form-group">
                                            <label for="address-title-billing">{{ trans('storefront::account.addresses.address_title') }}</label>

                                            <input
                                                name="address_title"
                                                type="text"
                                                id="address-title-billing"
                                                class="form-control"
                                                placeholder="{{ trans('storefront::account.addresses.address_title_placeholder_billing') }}"
                                                x-model="form.address_title"
                                            >

                                            <template x-if="errors.has('address_title')">
                                                <span class="error-message" x-text="errors.get('address_title')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="invoice-title">{{ trans('storefront::account.addresses.invoice_title') }}</label>

                                            <input
                                                name="invoice_title"
                                                type="text"
                                                id="invoice-title"
                                                class="form-control"
                                                x-model="form.invoice_title"
                                            >

                                            <template x-if="errors.has('invoice_title')">
                                                <span class="error-message" x-text="errors.get('invoice_title')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="invoice-tax-number">
                                                <span class="d-none d-sm-inline">{{ trans('storefront::account.addresses.invoice_tax_number') }}</span>
                                                <span class="d-inline d-sm-none">{{ trans('storefront::account.addresses.invoice_tax_number_short') }}</span>
                                            </label>

                                            <input
                                                name="invoice_tax_number"
                                                type="text"
                                                id="invoice-tax-number"
                                                class="form-control"
                                                x-model="form.invoice_tax_number"
                                            >

                                            <template x-if="errors.has('invoice_tax_number')">
                                                <span class="error-message" x-text="errors.get('invoice_tax_number')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="invoice-tax-office">{{ trans('storefront::account.addresses.invoice_tax_office') }}</label>

                                            <input
                                                name="invoice_tax_office"
                                                type="text"
                                                id="invoice-tax-office"
                                                class="form-control"
                                                x-model="form.invoice_tax_office"
                                            >

                                            <template x-if="errors.has('invoice_tax_office')">
                                                <span class="error-message" x-text="errors.get('invoice_tax_office')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="billing-email">{{ trans('storefront::account.addresses.billing_email') }}</label>

                                            <input
                                                name="billing_email"
                                                type="email"
                                                id="billing-email"
                                                class="form-control"
                                                x-model="form.billing_email"
                                            >

                                            <template x-if="errors.has('billing_email')">
                                                <span class="error-message" x-text="errors.get('billing_email')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-18">
                                        <div class="form-group">
                                            <label for="billing-address-1">
                                                {{ trans('storefront::account.addresses.street_address') }}<span>*</span>
                                            </label>

                                            <input
                                                name="address_1"
                                                type="text"
                                                id="billing-address-1"
                                                placeholder="{{ trans('storefront::account.addresses.address_line_1') }}"
                                                class="form-control"
                                                x-model="form.address_1"
                                            >

                                            <template x-if="errors.has('address_1')">
                                                <span class="error-message" x-text="errors.get('address_1')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="billing-city-id">{{ trans('storefront::account.addresses.state') }}<span>*</span></label>

                                            <select
                                                name="city_id"
                                                id="billing-city-id"
                                                class="form-control arrow-black"
                                                x-model="form.city_id"
                                                @change="changeCityId($event.target.value)"
                                            >
                                                <option value="">{{ trans('storefront::account.addresses.please_select') }}</option>
                                                <template x-for="p in provincesTR" :key="p.sehir_id">
                                                    <option :value="p.sehir_id" x-text="p.sehir_adi"></option>
                                                </template>
                                            </select>

                                            <template x-if="errors.has('city_id')">
                                                <span class="error-message" x-text="errors.get('city_id')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="billing-district-id">{{ trans('storefront::account.addresses.city') }}<span>*</span></label>

                                            <select
                                                name="district_id"
                                                id="billing-district-id"
                                                class="form-control arrow-black"
                                                x-model="form.district_id"
                                                @change="changeDistrictId($event.target.value)"
                                            >
                                                <option value="">{{ trans('storefront::account.addresses.please_select') }}</option>
                                                <template x-for="d in districtOptionsTR" :key="d.id">
                                                    <option :value="d.id" x-text="d.name"></option>
                                                </template>
                                            </select>

                                            <template x-if="errors.has('district_id')">
                                                <span class="error-message" x-text="errors.get('district_id')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9" x-cloak x-show="!singleCountry">
                                        <div class="form-group">
                                            <label for="billing-country">
                                                {{ trans('storefront::account.addresses.country') }}<span>*</span>
                                            </label>

                                            <select
                                                :value="form.country"
                                                name="country"
                                                id="billing-country"
                                                class="form-control arrow-black"
                                                @change="changeCountry($event.target.value)"
                                            >
                                                <template x-for="(name, code) in countries">
                                                    <option :value="code" x-text="name"></option>
                                                </template>
                                            </select>

                                            <template x-if="errors.has('country')">
                                                <span class="error-message" x-text="errors.get('country')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-9">
                                        <div class="form-group">
                                            <label for="phone-billing">{{ trans('storefront::account.addresses.phone') }}</label>

                                            <input
                                                type="tel"
                                                name="phone"
                                                id="phone-billing"
                                                class="form-control"
                                                x-model="form.phone"
                                            >

                                            <template x-if="errors.has('phone')">
                                                <span class="error-message" x-text="errors.get('phone')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="col-18 col-lg-18">
                                        <button
                                            type="button"
                                            class="btn btn-lg btn-default btn-cancel"
                                            @click="cancel"
                                        >
                                            {{ trans('storefront::account.addresses.cancel') }}
                                        </button>

                                        <button
                                            type="submit"
                                            class="btn btn-lg btn-primary btn-save-address"
                                            :class="{ 'btn-loading': loading }"
                                        >
                                            {{ trans('storefront::account.addresses.save_address') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

     </div>
@endsection

@push('globals')
    <script>
        FleetCart.langs['storefront::account.addresses.confirm'] = '{{ trans('storefront::account.addresses.confirm') }}';
    </script>

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/addresses/main.scss', 
        'modules/Storefront/Resources/assets/public/js/pages/account/addresses/main.js',
    ])
@endpush
