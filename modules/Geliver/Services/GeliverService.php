<?php

namespace Modules\Geliver\Services;

use Geliver\Client;
use GuzzleHttp\Client as GuzzleClient;
use Modules\Order\Entities\Order;

class GeliverService
{
    private Client $client;
    private GuzzleClient $http;

    public function __construct()
    {
        $token = config('services.geliver.token') ?: setting('geliver_api_token');
        if (!$token) {
            throw new \RuntimeException('Geliver API token gerekli.');
        }
        if (!class_exists(\Geliver\Client::class)) {
            $dir = base_path('vendor/geliver/sdk/src');
            if (is_dir($dir)) {
                $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
                foreach ($it as $file) {
                    if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
                        require_once $file->getPathname();
                    }
                }
            }
        }
        $cacert = config('services.geliver.cacert') ?: setting('geliver_cacert_path');
        $iniCurl = ini_get('curl.cainfo') ?: null;
        $iniOpenSsl = ini_get('openssl.cafile') ?: null;
        $verifyPath = null;
        if ($cacert && is_string($cacert) && file_exists($cacert)) {
            $verifyPath = $cacert;
        } elseif ($iniCurl && is_string($iniCurl) && file_exists($iniCurl)) {
            $verifyPath = $iniCurl;
        } elseif ($iniOpenSsl && is_string($iniOpenSsl) && file_exists($iniOpenSsl)) {
            $verifyPath = $iniOpenSsl;
        }
        $testMode = (bool) (config('services.geliver.test_mode') ?? setting('geliver_test_mode', true));
        if (!$verifyPath && !$testMode) {
            throw new \RuntimeException('CA sertifika yolu gerekli. GELIVER_CACERT_PATH veya php.ini curl.cainfo/openssl.cafile ayarlayın.');
        }
        $base = \Geliver\Client::DEFAULT_BASE_URL;
        $http = new GuzzleClient([
            'base_uri' => $base,
            'timeout' => 30.0,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'verify' => $verifyPath ? $verifyPath : ($testMode ? false : true),
        ]);
        $this->client = new Client($token, null, $http);
        $this->http = $http;
    }

    public function sendOrderToGeliver(Order $order): array
    {
        if ($order->geliver_shipment_id) {
            throw new \RuntimeException('Sipariş zaten Geliver’e gönderilmiş.');
        }

        $senderId = config('services.geliver.sender_address_id') ?: setting('geliver_sender_address_id');
        if (!$senderId) {
            throw new \RuntimeException('Sender Address ID gerekli.');
        }

        $shippingSnapshot = $order->shippingSnapshot;
        $shipping = $order->shippingAddress ?: null;
        $name = $shippingSnapshot
            ? trim((string) (($shippingSnapshot->first_name ?? '') . ' ' . ($shippingSnapshot->last_name ?? '')))
            : ($shipping ? ($shipping->first_name.' '.$shipping->last_name) : $order->customer_full_name);
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com';
        $defaultEmail = config('services.geliver.default_email') ?: setting('geliver_default_email');
        $email = $order->customer_email ?: ($shipping->email ?? null) ?: ($defaultEmail ?: ('no-reply@' . $host));
        $phoneRaw = ($shippingSnapshot?->phone ?: null) ?: ($shipping ? $shipping->phone : null) ?: $order->customer_phone;
        $phone = $this->normalizePhone($phoneRaw);
        $address1 = ($shippingSnapshot?->address_line ?: null) ?: ($shipping ? $shipping->address_1 : null);
        $address2 = ($shippingSnapshot?->address_2 ?: null) ?: ($shipping ? ($shipping->address_2 ?: null) : null);
        $cityName = ($shippingSnapshot?->city ?: null) ?: ($shipping ? $shipping->city_title : null);
        $districtName = ($shippingSnapshot?->district ?: null) ?: ($shipping ? $shipping->district_title : null);
        $zip = ($shippingSnapshot?->zip ?: null) ?: ($shipping ? $shipping->zip : null);
        $countryCode = 'TR';
        $cityCode = '';
        if (!$cityCode && $cityName) {
            $resolved = $this->resolveCityCode($countryCode, $cityName);
            if ($resolved) { $cityCode = $resolved; }
        }
        if (!$cityCode) {
            throw new \RuntimeException('Şehir kodu bulunamadı.');
        }
        $districtId = null;
        if (!$districtName) {
            $districtName = 'Merkez';
        }
        if ($districtName) {
            $districtId = $this->resolveDistrictId($countryCode, $cityCode, $districtName);
        }
        $zip = $zip ?: (string) (config('services.geliver.default_zip') ?: setting('geliver_default_zip', '00000'));

        $length = (string) (config('services.geliver.default_length') ?: setting('geliver_default_length', 10.0));
        $width = (string) (config('services.geliver.default_width') ?: setting('geliver_default_width', 10.0));
        $height = (string) (config('services.geliver.default_height') ?: setting('geliver_default_height', 10.0));
        $weight = (string) (config('services.geliver.default_weight') ?: setting('geliver_default_weight', 1.0));

        $orderNumber = (string) $order->id;
        $sourceIdentifier = config('app.url');
        $totalAmount = (string) $order->total->amount();
        $totalCurrency = $order->currency ?: 'TRY';

        $recipientBody = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address1' => $address1,
            'address2' => $address2,
            'countryCode' => $countryCode,
            'cityName' => $cityName,
            'cityCode' => $cityCode,
            'districtName' => $districtName,
        ];
        $recipientBody['zip'] = $zip;
        if ($districtId) {
            $recipientBody['districtID'] = $districtId;
        }
        if (!$phone) { throw new \RuntimeException('Alıcı telefon gerekli.'); }
        if (!$address1) { throw new \RuntimeException('Adres satırı gerekli.'); }
        if (!$cityName) { throw new \RuntimeException('Şehir adı gerekli.'); }
        if (!$districtName) { throw new \RuntimeException('İlçe adı gerekli.'); }

        try {
            $recipient = $this->client->addresses()->createRecipient($recipientBody);
        } catch (\Geliver\ApiException $e) {
            $msg = $e->getMessage();
            $addl = property_exists($e, 'additionalMessage') ? $e->additionalMessage : null;
            $codeStr = property_exists($e, 'codeStr') ? $e->codeStr : null;
            $detail = $addl ? ($msg . ' - ' . $addl) : $msg;
            if ($codeStr) { $detail .= ' [' . $codeStr . ']'; }
            throw new \RuntimeException($detail, $e->status);
        }
        $recipientId = $this->extractAddressId($recipient);
        if (!$recipientId) {
            throw new \RuntimeException('Alıcı adresi oluşturulamadı.');
        }

        $payload = [
            'order' => [
                'orderNumber' => $orderNumber,
                'sourceIdentifier' => $sourceIdentifier,
                'totalAmount' => $totalAmount,
                'totalAmountCurrency' => $totalCurrency,
            ],
            'senderAddressID' => $senderId,
            'recipientAddressID' => $recipientId,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'weight' => $weight,
            'distanceUnit' => 'cm',
            'massUnit' => 'kg',
        ];

        $items = [];
        foreach ($order->products as $op) {
            $unitPriceAtOrder = method_exists($op, 'getUnitPriceAtOrderAttribute') ? ($op->unit_price_at_order?->amount() ?? null) : null;
            $unitPrice = $unitPriceAtOrder ?? ($op->unit_price?->amount() ?? null);
            $variantParts = [];
            foreach ($op->variations as $var) {
                $val = $var->value ?? null;
                if ($val) { $variantParts[] = $val; }
            }
            $variantTitle = implode(', ', $variantParts);
            $optionParts = [];
            foreach ($op->options as $opt) {
                $vals = [];
                foreach ($opt->values as $v) {
                    $label = $v->label ?? ($v->name ?? null);
                    if ($label) { $vals[] = $label; }
                }
                $optName = $opt->name ?? null;
                if ($optName && !empty($vals)) {
                    $optionParts[] = $optName . ': ' . implode(', ', $vals);
                }
            }
            $optionsText = implode('; ', $optionParts);
            $qtyLabel = method_exists($op, 'getFormattedQuantityWithUnit') ? $op->getFormattedQuantityWithUnit() : null;
            $titleBase = (string) $op->name;
            $title = $titleBase;
            if ($variantTitle !== '') { $title .= ' - ' . $variantTitle; }
            if ($optionsText !== '') { $title .= ' - ' . $optionsText; }
            if ($qtyLabel) { $title .= ' (' . $qtyLabel . ')'; }
            $qtyNumeric = (int) max(1, (int) floor((float) ($op->qty ?? 1)));
            $item = [
                'title' => $title,
                'sku' => (string) $op->sku,
                'quantity' => $qtyNumeric,
                'currency' => $totalCurrency,
            ];
            if ($unitPrice !== null) { $item['unitPrice'] = (string) $unitPrice; }
            if ($variantTitle !== '') { $item['variantTitle'] = $variantTitle; }
            if ($optionsText !== '') { $item['options'] = $optionsText; }
            $items[] = $item;
        }
        if (!empty($items)) {
            $payload['items'] = $items;
        }

        // zip already included in recipient address creation when present

        $testMode = (bool) (config('services.geliver.test_mode') ?? setting('geliver_test_mode', true));
        $shipment = $testMode
            ? $this->client->shipments()->createTest($payload)
            : $this->client->shipments()->create($payload);

        $shipmentId = $this->extractShipmentId($shipment);
        $order->geliver_shipment_id = $shipmentId;
        $order->geliver_shipment_payload = json_encode($shipment);
        $order->geliver_last_status = $this->extractShipmentStatus($shipment);
        $order->geliver_last_status_at = now();

        // Extract and save generic tracking information if available
        $trackingNumber = $this->extractTrackingNumber($shipment);
        $carrierName = $this->extractCarrierName($shipment);
        $trackingUrl = $this->extractTrackingUrl($shipment);

        if ($trackingNumber) {
            $order->shipping_tracking_number = (string) $trackingNumber;
        }

        if ($carrierName) {
            $order->shipping_carrier_name = is_array($carrierName) ? ($carrierName['name'] ?? json_encode($carrierName)) : (string) $carrierName;
        }

        if ($trackingUrl && filter_var($trackingUrl, FILTER_VALIDATE_URL)) {
            $order->shipping_tracking_url = (string) $trackingUrl;
            $order->tracking_reference = (string) $trackingUrl;
        }

        if (empty($order->tracking_reference) && $trackingNumber) {
            $order->tracking_reference = (string) $trackingNumber;
        }

        $order->save();

        return $shipment;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $digits = preg_replace('/\D+/', '', $phone);
        if (!$digits) return null;
        if (strlen($digits) === 11 && substr($digits, 0, 1) === '0') {
            $digits = substr($digits, 1);
        }
        if (strlen($digits) === 10 && substr($digits, 0, 1) === '5') {
            $digits = '90' . $digits;
        }
        if (substr($digits, 0, 3) === '090' && strlen($digits) === 13) {
            $digits = substr($digits, 1);
        }
        if (substr($digits, 0, 2) !== '90') {
            if (strlen($digits) === 12 && substr($digits, 0, 1) === '9') {
                // already something like 90xxxxxxxxxx
            } else {
                // fallback: ensure Turkey country code
                $digits = '90' . ltrim($digits, '0');
            }
        }
        return '+' . $digits;
    }

    private function resolveCityCode(string $countryCode, string $cityName): ?string
    {
        $res = $this->client->geo()->listCities($countryCode);
        $items = [];
        if (is_array($res)) {
            if (isset($res['data']) && is_array($res['data'])) {
                $items = $res['data'];
            } else {
                $items = $res;
            }
        }
        $t = function_exists('mb_strtolower') ? mb_strtolower(trim($cityName), 'UTF-8') : strtolower(trim($cityName));
        foreach ($items as $it) {
            if (!is_array($it)) { continue; }
            $name = $it['name'] ?? ($it['title'] ?? ($it['cityName'] ?? null));
            $code = $it['code'] ?? ($it['cityCode'] ?? null);
            if (!$name || !$code) { continue; }
            $n = function_exists('mb_strtolower') ? mb_strtolower(trim((string) $name), 'UTF-8') : strtolower(trim((string) $name));
            if ($n === $t) { return (string) $code; }
        }
        return null;
    }

    private function resolveDistrictId(string $countryCode, string $cityCode, string $districtName): ?int
    {
        $res = $this->client->geo()->listDistricts($countryCode, $cityCode);
        $items = [];
        if (is_array($res)) {
            if (isset($res['data']) && is_array($res['data'])) {
                $items = $res['data'];
            } else {
                $items = $res;
            }
        }
        $t = function_exists('mb_strtolower') ? mb_strtolower(trim($districtName), 'UTF-8') : strtolower(trim($districtName));
        foreach ($items as $it) {
            if (!is_array($it)) { continue; }
            $name = $it['name'] ?? ($it['title'] ?? ($it['districtName'] ?? null));
            $id = $it['id'] ?? ($it['districtID'] ?? null);
            if (!$name || !$id) { continue; }
            $n = function_exists('mb_strtolower') ? mb_strtolower(trim((string) $name), 'UTF-8') : strtolower(trim((string) $name));
            if ($n === $t) { return (int) $id; }
        }
        return null;
    }

    private function extractAddressId($payload): ?string
    {
        if (is_array($payload)) {
            if (isset($payload['id'])) return (string) $payload['id'];
            if (isset($payload['addressID'])) return (string) $payload['addressID'];
            if (isset($payload['data']) && is_array($payload['data'])) {
                $d = $payload['data'];
                if (isset($d['id'])) return (string) $d['id'];
                if (isset($d['addressID'])) return (string) $d['addressID'];
            }
            foreach ($payload as $k => $v) {
                if (is_array($v)) {
                    $id = $this->extractAddressId($v);
                    if ($id) return $id;
                }
            }
        }
        return null;
    }

    private function extractShipmentId($payload): ?string
    {
        if (is_array($payload)) {
            if (isset($payload['id'])) return (string) $payload['id'];
            if (isset($payload['shipmentID'])) return (string) $payload['shipmentID'];
            if (isset($payload['data']) && is_array($payload['data'])) {
                $d = $payload['data'];
                if (isset($d['id'])) return (string) $d['id'];
                if (isset($d['shipmentID'])) return (string) $d['shipmentID'];
            }
            foreach ($payload as $k => $v) {
                if (is_array($v)) {
                    $id = $this->extractShipmentId($v);
                    if ($id) return $id;
                }
            }
        }
        return null;
    }

    private function extractShipmentStatus($payload): ?string
    {
        if (is_array($payload)) {
            if (isset($payload['status'])) return (string) $payload['status'];
            if (isset($payload['statusCode'])) return (string) $payload['statusCode'];
            if (isset($payload['data']) && is_array($payload['data'])) {
                $d = $payload['data'];
                if (isset($d['status'])) return (string) $d['status'];
                if (isset($d['statusCode'])) return (string) $d['statusCode'];
            }
        }
        return null;
    }

    public function fetchShipmentById(string $shipmentId): ?array
    {
        try {
            $res = $this->client->shipments()->get($shipmentId);
            if (is_array($res)) return $res;
        } catch (\Throwable $e) {
        }
        try {
            $resp = $this->http->get('/shipments/' . urlencode($shipmentId));
            $body = (string) $resp->getBody();
            $decoded = json_decode($body, true);
            if (is_array($decoded)) return $decoded;
        } catch (\Throwable $e) {
        }
        return null;
    }

    public function extractTrackingNumber($payload): ?string
    {
        if (!is_array($payload)) return null;

        $v = data_get($payload, 'trackingNo')
            ?? data_get($payload, 'tracking_no')
            ?? data_get($payload, 'tracking_number')
            ?? data_get($payload, 'trackingNumber')
            ?? data_get($payload, 'trackingId')
            ?? data_get($payload, 'tracking_id')
            ?? data_get($payload, 'tracking_code')
            ?? data_get($payload, 'trackingCode')
            ?? data_get($payload, 'shipment.trackingNo')
            ?? data_get($payload, 'shipment.tracking_no')
            ?? data_get($payload, 'shipment.tracking_number')
            ?? data_get($payload, 'shipment.trackingNumber')
            ?? data_get($payload, 'data.trackingNo')
            ?? data_get($payload, 'data.tracking_no')
            ?? data_get($payload, 'data.tracking_number')
            ?? data_get($payload, 'data.trackingNumber')
            ?? data_get($payload, 'trackingStatus.trackingNo')
            ?? data_get($payload, 'trackingStatus.tracking_no')
            ?? data_get($payload, 'trackingStatus.tracking_number')
            ?? data_get($payload, 'trackingStatus.trackingNumber')
            ?? data_get($payload, 'data.trackingStatus.trackingNo')
            ?? data_get($payload, 'data.trackingStatus.tracking_no')
            ?? data_get($payload, 'data.trackingStatus.tracking_number')
            ?? data_get($payload, 'data.trackingStatus.trackingNumber')
            ?? data_get($payload, 'shipment.trackingStatus.trackingNo')
            ?? data_get($payload, 'shipment.trackingStatus.tracking_no')
            ?? data_get($payload, 'shipment.trackingStatus.tracking_number')
            ?? data_get($payload, 'shipment.trackingStatus.trackingNumber')
            ?? data_get($payload, 'trackingHeader.trackingNo')
            ?? data_get($payload, 'tracking_info.tracking_no')
            ?? data_get($payload, 'tracking_info.tracking_number')
            ?? data_get($payload, 'shipment_tracking_number')
            ?? data_get($payload, 'awb')
            ?? data_get($payload, 'awb_number')
            ?? data_get($payload, 'awbNo')
            ?? data_get($payload, 'waybillNo')
            ?? data_get($payload, 'waybill_no')
            ?? data_get($payload, 'waybill_number')
            ?? data_get($payload, 'waybillNumber');

        if (!$v) {
            $link = $this->extractTrackingUrl($payload);
            if ($link) {
                $q = parse_url($link, PHP_URL_QUERY);
                if ($q) {
                    parse_str($q, $qp);
                    $v = $qp['code'] ?? ($qp['tracking_no'] ?? ($qp['trackingNo'] ?? ($qp['id'] ?? ($qp['t'] ?? null))));
                }
            }
        }

        return $v ? (string) $v : null;
    }

    public function extractCarrierName($payload): ?string
    {
        if (!is_array($payload)) return null;

        $v = data_get($payload, 'carrier')
            ?? data_get($payload, 'carrierName')
            ?? data_get($payload, 'carrier.name')
            ?? data_get($payload, 'providerName')
            ?? data_get($payload, 'provider_name')
            ?? data_get($payload, 'providerCode')
            ?? data_get($payload, 'provider_code')
            ?? data_get($payload, 'data.carrier')
            ?? data_get($payload, 'data.carrierName')
            ?? data_get($payload, 'data.carrier.name')
            ?? data_get($payload, 'data.providerName')
            ?? data_get($payload, 'data.provider_name')
            ?? data_get($payload, 'data.providerCode')
            ?? data_get($payload, 'data.provider_code')
            ?? data_get($payload, 'trackingStatus.carrier')
            ?? data_get($payload, 'trackingStatus.carrierName')
            ?? data_get($payload, 'trackingStatus.carrier.name')
            ?? data_get($payload, 'trackingStatus.providerName')
            ?? data_get($payload, 'trackingStatus.provider_name')
            ?? data_get($payload, 'trackingStatus.providerCode')
            ?? data_get($payload, 'trackingStatus.provider_code')
            ?? data_get($payload, 'data.trackingStatus.carrier')
            ?? data_get($payload, 'data.trackingStatus.carrierName')
            ?? data_get($payload, 'data.trackingStatus.carrier.name')
            ?? data_get($payload, 'data.trackingStatus.providerName')
            ?? data_get($payload, 'data.trackingStatus.provider_name')
            ?? data_get($payload, 'data.trackingStatus.providerCode')
            ?? data_get($payload, 'data.trackingStatus.provider_code')
            ?? data_get($payload, 'shipment.trackingStatus.carrier')
            ?? data_get($payload, 'shipment.trackingStatus.carrierName')
            ?? data_get($payload, 'shipment.trackingStatus.carrier.name')
            ?? data_get($payload, 'shipment.trackingStatus.providerName')
            ?? data_get($payload, 'shipment.trackingStatus.provider_name')
            ?? data_get($payload, 'shipment.trackingStatus.providerCode')
            ?? data_get($payload, 'shipment.trackingStatus.provider_code')
            ?? data_get($payload, 'shipment.providerName')
            ?? data_get($payload, 'shipment.provider_name')
            ?? data_get($payload, 'shipment.providerCode')
            ?? data_get($payload, 'shipment.provider_code')
            ?? data_get($payload, 'shipment.carrier')
            ?? data_get($payload, 'shipment.carrierName')
            ?? data_get($payload, 'shipment.carrier.name');

        return is_array($v) ? ($v['name'] ?? json_encode($v)) : ($v ? (string) $v : null);
    }

    public function extractTrackingUrl($payload): ?string
    {
        if (!is_array($payload)) return null;

        $v = data_get($payload, 'tracking_url')
            ?? data_get($payload, 'tracking_link')
            ?? data_get($payload, 'trackingUrl')
            ?? data_get($payload, 'trackingLink')
            ?? data_get($payload, 'trackingStatus.tracking_url')
            ?? data_get($payload, 'trackingStatus.tracking_link')
            ?? data_get($payload, 'trackingStatus.trackingUrl')
            ?? data_get($payload, 'trackingStatus.trackingLink')
            ?? data_get($payload, 'data.tracking_url')
            ?? data_get($payload, 'data.tracking_link')
            ?? data_get($payload, 'data.trackingUrl')
            ?? data_get($payload, 'data.trackingLink')
            ?? data_get($payload, 'data.trackingStatus.tracking_url')
            ?? data_get($payload, 'data.trackingStatus.tracking_link')
            ?? data_get($payload, 'data.trackingStatus.trackingUrl')
            ?? data_get($payload, 'data.trackingStatus.trackingLink')
            ?? data_get($payload, 'shipment.trackingStatus.tracking_url')
            ?? data_get($payload, 'shipment.trackingStatus.tracking_link')
            ?? data_get($payload, 'shipment.trackingStatus.trackingUrl')
            ?? data_get($payload, 'shipment.trackingStatus.trackingLink')
            ?? data_get($payload, 'shipment.tracking_url')
            ?? data_get($payload, 'shipment.tracking_link')
            ?? data_get($payload, 'shipment.trackingUrl')
            ?? data_get($payload, 'shipment.trackingLink')
            ?? data_get($payload, 'tracking.link')
            ?? data_get($payload, 'shipment.tracking.link')
            ?? data_get($payload, 'data.tracking.link');

        return $v ? (string) $v : null;
    }
}
