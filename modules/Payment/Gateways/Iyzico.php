<?php

namespace Modules\Payment\Gateways;

use Exception;
use Iyzipay\Options;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\Locale;
use Iyzipay\Model\Address;
use Illuminate\Http\Request;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\PaymentGroup;
use Modules\Order\Entities\Order;
use Iyzipay\Model\BasketItemType;
use Modules\Payment\GatewayInterface;
use Iyzipay\Model\CheckoutFormInitialize;
use Modules\Payment\Responses\IyzicoResponse;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;

class Iyzico implements GatewayInterface
{
    public $label;
    public $description;
    public const CURRENCIES = [
        "TRY",
        "EUR",
        "USD",
        "GBP",
        "IRR",
        "NOK",
        "RUB",
        "CHF",
    ];
    public Order $order;


    public function __construct()
    {
        $this->label = setting('iyzico_label');
        $this->description = setting('iyzico_description');
    }


    /**
     * @throws Exception
     */
    public function purchase(Order $order, Request $request)
    {
        $this->checkIfCurrentCurrencyIsSupported();

        $this->order = $order;
        $apiOptions = $this->prepareApiOptions();
        $apiRequest = $this->prepareApiRequest();

        $response = CheckoutFormInitialize::create($apiRequest, $apiOptions);

        if($response->getPaymentPageUrl() == null) {
            throw new Exception(trans('core::messages.something_went_wrong'));
        }

        return new IyzicoResponse($order, $response);
    }


    /**
     * @throws Exception
     */
    private function checkIfCurrentCurrencyIsSupported(): void
    {
        if (!in_array(currency(), setting('iyzico_supported_currencies') ?? self::CURRENCIES)) {
            throw new Exception(trans('payment::messages.currency_not_supported'));
        }
    }


    public function complete(Order $order)
    {
        return new IyzicoResponse($order, request());
    }


    private function prepareApiOptions(): Options
    {
        $options = new Options();

        $options->setApiKey(setting('iyzico_api_key'));
        $options->setSecretKey(setting('iyzico_api_secret'));
        $options->setBaseUrl(
            setting('iyzico_test_mode')
                ? 'https://sandbox-api.iyzipay.com'
                : 'https://api.iyzipay.com'
        );

        return $options;
    }


    private function prepareApiRequest(): CreateCheckoutFormInitializeRequest
    {
        $apiRequest = new CreateCheckoutFormInitializeRequest();

        $buyer = $this->prepareBuyer();
        $shippingAddress = $this->prepareShippingAddress();
        $billingAddress = $this->prepareBillingAddress();
        $basketItems = $this->prepareBasketItems();

        $apiRequest->setLocale(
            locale() === 'tr'
                ? Locale::TR
                : Locale::EN
        );
        $apiRequest->setConversationId(time());
        $apiRequest->setPrice($this->order->sub_total);
        $apiRequest->setPaidPrice($this->order->total);
        $apiRequest->setCurrency(currency());
        $apiRequest->setBasketId($this->order->id);
        $apiRequest->setPaymentGroup(PaymentGroup::PRODUCT);
        $apiRequest->setEnabledInstallments(['1']);
        $apiRequest->setCallbackUrl(
            $this->getRedirectUrl($this->order)
        );
        $apiRequest->setForceThreeDS(1);
        $apiRequest->setBuyer($buyer);
        $apiRequest->setShippingAddress($shippingAddress);
        $apiRequest->setBillingAddress($billingAddress);
        $apiRequest->setBasketItems($basketItems);

        return $apiRequest;
    }


    private function getRedirectUrl($order): string
    {
        return route('checkout.complete.store', [
            'orderId' => $order->id,
            'paymentMethod' => 'iyzico',
            'reference' => uniqid('ref_'),
        ]);
    }


    private function prepareBuyer(): Buyer
    {
        $buyer = new Buyer();

        $billingSnap = $this->order->billingSnapshot;
        $billingAddr = $this->order->billingAddress;
        $billing = $billingSnap ?: $billingAddr;
        $billingCountry = $billing?->country ?? 'TR';

        $buyer->setId($this->order->customer_id ?? uniqid('guest_'));
        $buyer->setName($this->order->customer_first_name);
        $buyer->setSurname($this->order->customer_last_name);
        $buyer->setGsmNumber($this->order->customer_phone);
        $buyer->setEmail($this->order->customer_email);
        $buyer->setIdentityNumber(uniqid('iyzico_'));
        $buyer->setRegistrationAddress(trim((string) (($billing?->address_line ?? ($billing?->address_1 ?? '')) . ', ' . ($billing?->address_2 ?? ''))));
        $buyer->setCity($billing?->city ?? $billing?->city_title);
        $buyer->setCountry($billingCountry);
        $buyer->setZipCode($billing?->zip);

        return $buyer;
    }


    private function prepareBillingAddress(): Address
    {
        $billingAddress = new Address();

        $billingSnap = $this->order->billingSnapshot;
        $billingAddr = $this->order->billingAddress;
        $billing = $billingSnap ?: $billingAddr;
        $billingCountry = $billing?->country ?? 'TR';

        $billingAddress->setContactName($this->order->billing_full_name);
        $billingAddress->setCity($billing?->city ?? $billing?->city_title);
        $billingAddress->setCountry($billingCountry);
        $billingAddress->setAddress(trim((string) (($billing?->address_line ?? ($billing?->address_1 ?? '')) . ', ' . ($billing?->address_2 ?? ''))));
        $billingAddress->setZipCode($billing?->zip);

        return $billingAddress;
    }


    private function prepareShippingAddress(): Address
    {
        $shippingAddress = new Address();

        $shippingSnap = $this->order->shippingSnapshot;
        $shippingAddr = $this->order->shippingAddress;
        $billingSnap = $this->order->billingSnapshot;
        $billingAddr = $this->order->billingAddress;

        $shipping = $shippingSnap ?: $shippingAddr;
        $billing = $billingSnap ?: $billingAddr;

        $shippingCountry = $shipping?->country ?? ($billing?->country ?? 'TR');

        $shippingAddress->setContactName($this->order->shipping_full_name);
        $shippingAddress->setCity(($shipping?->city ?? $shipping?->city_title) ?? ($billing?->city ?? $billing?->city_title));
        $shippingAddress->setCountry($shippingCountry);
        $shippingAddress->setAddress(trim((string) ((
            $shipping?->address_line
            ?? ($shipping?->address_1 ?? null)
            ?? ($billing?->address_line ?? null)
            ?? ($billing?->address_1 ?? '')
        ) . ', ' . ($shipping?->address_2 ?? ''))));
        $shippingAddress->setZipCode($shipping?->zip ?? $billing?->zip);

        return $shippingAddress;
    }


    private function prepareBasketItems(): array
    {
        $basketItems = [];

        foreach ($this->order->products as $orderProduct) {
            $basketItems[] = $this->prepareBasketItem($orderProduct);
        }

        return $basketItems;
    }


    private function prepareBasketItem($orderProduct): BasketItem
    {
        $basketItem = new BasketItem();

        $basketItem->setId($orderProduct->product->id);
        $basketItem->setName($orderProduct->product->name);
        $basketItem->setCategory1(
            $orderProduct->product->categories->isNotEmpty()
                ? implode(',', $orderProduct->product->categories->pluck('name')->toArray())
                : 'Uncategorized'
        );
        $basketItem->setItemType(
            $orderProduct->product->is_virtual
                ? BasketItemType::VIRTUAL
                : BasketItemType::PHYSICAL
        );
        $basketItem->setPrice(
            (float)$orderProduct
                ->line_total
                ->convertToCurrentCurrency()
                ->amount()
        );

        return $basketItem;
    }
}
