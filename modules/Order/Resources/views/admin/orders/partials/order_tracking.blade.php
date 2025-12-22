<div class="order-tracking-wrapper">
    <h4 class="section-title">{{ trans('order::orders.order_tracking') }}</h4>

    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-5 col-md-8">
                <label for="shipping_carrier_name">{{ trans('order::orders.shipping_carrier_name') }}</label>

                <div class="form-group">
                    <input
                        type="text"
                        name="shipping_carrier_name"
                        id="shipping_carrier_name"
                        data-id="{{ $order->id }}"
                        class="form-control @error('shipping_carrier_name') is-invalid @enderror"
                        value="{{ old('shipping_carrier_name', $order->shipping_carrier_name) }}"
                        placeholder="{{ trans('order::orders.shipping_carrier_name_placeholder') }}"
                    >

                    @error('shipping_carrier_name')
                        <span class="help-block text-red">
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <label for="shipping_tracking_number">{{ trans('order::orders.shipping_tracking_number') }}</label>

                <div class="form-group">
                    <input
                        type="text"
                        name="shipping_tracking_number"
                        id="shipping_tracking_number"
                        data-id="{{ $order->id }}"
                        class="form-control @error('shipping_tracking_number') is-invalid @enderror"
                        value="{{ old('shipping_tracking_number', $order->shipping_tracking_number) }}"
                        placeholder="{{ trans('order::orders.shipping_tracking_number_placeholder') }}"
                    >

                    @error('shipping_tracking_number')
                        <span class="help-block text-red">
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <label for="shipping_tracking_url">{{ trans('order::orders.shipping_tracking_url') }}</label>

                <div class="form-group">
                    <input
                        type="text"
                        name="shipping_tracking_url"
                        id="shipping_tracking_url"
                        data-id="{{ $order->id }}"
                        class="form-control @error('shipping_tracking_url') is-invalid @enderror"
                        value="{{ old('shipping_tracking_url', $order->shipping_tracking_url) }}"
                        placeholder="{{ trans('order::orders.shipping_tracking_url_placeholder') }}"
                    >

                    @error('shipping_tracking_url')
                        <span class="help-block text-red">
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="text-left mt-3">
                    <button type="submit" class="btn btn-primary">
                        {{ trans('admin::admin.buttons.save') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
