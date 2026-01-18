<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sipariş Durumu</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style type="text/css">
    img { max-width:100% !important; height:auto !important; }
    .email-container { width:100% !important; max-width:600px !important; }
    .stack-column,
    .stack-column td { vertical-align:top; }
    .steps-mobile { display:none; }
    @media screen and (max-width: 480px) {
      .email-wrap { padding:16px 8px !important; }
      .email-container { width:100% !important; }
      .stack-column { display:block !important; width:100% !important; max-width:100% !important; padding-left:0 !important; padding-right:0 !important; }
      .stack-column + .stack-column { margin-top:12px !important; }
      .steps-desktop { display:none !important; }
      .steps-mobile { display:table !important; width:100% !important; }
    }
  </style>
</head>

<body style="margin:0;padding:0;background:#f3f4f6;">
<?php
    $storeName = (string) (setting('store_name') ?: config('app.name') ?: '');
    $logoUrl = $logo ?? null;

    if (!isset($order)) {
        $order = null;
    }

    $esc = function ($v): string {
        return e((string) ($v ?? ''));
    };

    if (!isset($getAddr) || !is_callable($getAddr)) {
        $getAddr = function ($snapshot, $address, string $key, $default = null) {
            $val = data_get($snapshot, $key);
            if ($val === null || $val === '') {
                $val = data_get($address, $key);
            }

            return ($val === null || $val === '') ? $default : $val;
        };
    }

    if (!isset($fmtMoney) || !is_callable($fmtMoney)) {
        $fmtMoney = function ($money) {
            if (function_exists('format_price')) {
                try {
                    return format_price($money);
                } catch (\Throwable $e) {
                }
            }

            if (is_numeric($money)) {
                return number_format((float) $money, 2, ',', '.') . ' ₺';
            }

            if (is_object($money)) {
                try {
                    if (method_exists($money, 'format')) {
                        return $money->format();
                    }
                    if (method_exists($money, '__toString')) {
                        return (string) $money;
                    }
                } catch (\Throwable $e) {
                    return null;
                }
            }

            return null;
        };
    }

    if (!isset($products)) {
        $products = data_get($order, 'products');
        if (!($products instanceof \Illuminate\Support\Collection) && !is_array($products)) {
            $products = [];
        }
    }

    if (!isset($shippingSnapshot)) {
        $shippingSnapshot = data_get($order, 'shippingSnapshot');
    }
    if (!isset($billingSnapshot)) {
        $billingSnapshot = data_get($order, 'billingSnapshot');
    }
    if (!isset($shippingAddress)) {
        $shippingAddress = data_get($order, 'shippingAddress');
    }
    if (!isset($billingAddress)) {
        $billingAddress = data_get($order, 'billingAddress');
    }

    $statusKey = (string) (data_get($order, 'status') ?? \Modules\Order\Entities\Order::ON_THE_WAY);

    $map = [
        \Modules\Order\Entities\Order::SHIPPED => [
            'badge' => 'Kargoya verildi',
            'title' => 'Siparişiniz kargoya verildi',
            'desc' => 'Siparişiniz kargo firmasına teslim edildi. Takip linki varsa aşağıdan ulaşabilirsiniz.',
            'call_title' => 'Siparişiniz kargoya verildi',
            'call_desc' => 'Kargonuz çıkış şubesinden hareket etmek üzere hazırlanıyor. Takip numarası oluştuysa bu e-postada görebilirsiniz.',
            'step' => 1,
            'icon' => '📦',
        ],
        \Modules\Order\Entities\Order::ON_THE_WAY => [
            'badge' => 'Yolda',
            'title' => 'Siparişiniz yolda',
            'desc' => 'Siparişiniz transfer sürecinde. Tahmini teslimat için takip ekranını kontrol edebilirsiniz.',
            'call_title' => 'Siparişiniz yolda',
            'call_desc' => 'Kargonuz transfer merkezleri arasında ilerliyor. Tahmini teslimat tarihi kargo firmasına göre değişebilir.',
            'step' => 2,
            'icon' => '🚚',
        ],
        \Modules\Order\Entities\Order::OUT_FOR_DELIVERY => [
            'badge' => 'Dağıtımda',
            'title' => 'Siparişiniz dağıtımda',
            'desc' => 'Siparişiniz kurye tarafından dağıtıma çıkarıldı. Gün içinde teslim edilmesi bekleniyor.',
            'call_title' => 'Siparişiniz dağıtıma çıktı',
            'call_desc' => 'Kurye şu anda adresinize doğru yola çıktı. Lütfen telefonunuzu açık tutunuz.',
            'step' => 3,
            'icon' => '🛵',
        ],
        \Modules\Order\Entities\Order::COMPLETED => [
            'badge' => 'Teslim edildi',
            'title' => 'Siparişiniz teslim edildi',
            'desc' => 'Siparişiniz başarıyla teslim edildi. Bizi tercih ettiğiniz için teşekkür ederiz.',
            'call_title' => 'Teslim edildi',
            'call_desc' => 'Siparişiniz teslim edildi. Herhangi bir sorun varsa destek ekibimizle iletişime geçebilirsiniz.',
            'step' => 4,
            'icon' => '✅',
        ],
    ];

    $s = $map[$statusKey] ?? $map[\Modules\Order\Entities\Order::ON_THE_WAY];
    $fillMap = [1 => '18%', 2 => '50%', 3 => '82%', 4 => '100%'];
    $fill = $fillMap[(int) ($s['step'] ?? 2)] ?? '50%';

    $trackingUrl = null;
    $ref = data_get($order, 'tracking_reference');
    $trkUrlField = data_get($order, 'shipping_tracking_url');
    
    if (is_string($ref) && filter_var($ref, FILTER_VALIDATE_URL)) {
        $trackingUrl = $ref;
    } elseif (is_string($trkUrlField) && filter_var($trkUrlField, FILTER_VALIDATE_URL)) {
        $trackingUrl = $trkUrlField;
    }

    if (!isset($homeUrl)) {
        $homeUrl = \Route::has('home') ? route('home') : url('/');
    }
    if (!isset($accountUrl)) {
        $accountUrl = \Route::has('account.dashboard.index') ? route('account.dashboard.index') : $homeUrl;
    }

    $orderDetailsUrl = null;
    if (\Route::has('account.orders.show')) {
        $orderDetailsUrl = route('account.orders.show', $order);
    } elseif (\Route::has('account.orders.index')) {
        $orderDetailsUrl = route('account.orders.index');
    }

    $isCompleted = ($statusKey === \Modules\Order\Entities\Order::COMPLETED);
    
    $primaryCtaUrl = ($trackingUrl && !$isCompleted) ? $trackingUrl : $orderDetailsUrl;
    $primaryCtaText = ($trackingUrl && !$isCompleted) ? 'Kargo Takibi' : 'Sipariş Detayına Git';

    $secondaryCtaUrl = $orderDetailsUrl ?: $accountUrl;
    $secondaryCtaText = 'Sipariş Detayına Git';

    $paymentMethod = (string) (data_get($order, 'payment_method') ?? '');
    $orderNo = (string) (data_get($order, 'order_number') ?: data_get($order, 'id') ?: '');
    $trackingNo = (string) (data_get($order, 'shipping_tracking_number') ?? '');
    $carrierName = (string) (
        data_get($order, 'shipping_carrier_name')
            ?: data_get($order, 'shipping_method')
            ?: data_get($order, 'carrier')
            ?: ''
    );
?>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;">
  <tr>
    <td align="center" class="email-wrap" style="padding:24px 12px;">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" class="email-container" style="width:100%; max-width:600px;">
        <tr>
          <td style="background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; box-shadow:0 10px 24px rgba(17,24,39,.06); overflow:hidden;">

            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td style="background:#ff6a00; padding:18px 18px 16px 18px;">
                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="left" style="font-family:'Poppins',Arial,Helvetica,sans-serif; color:#ffffff; font-size:14px; font-weight:800; letter-spacing:.2px;">

                        <?php if (!empty($logoUrl)): ?>
                          <img src="<?php echo $esc($logoUrl); ?>" alt="<?php echo $esc($storeName); ?>" style="max-height:44px; max-width:180px; display:block;">
                        <?php else: ?>
                          <?php echo $esc($storeName); ?>
                        <?php endif; ?>
                      </td>
                      <td align="right" style="font-family:'Poppins',Arial,Helvetica,sans-serif;">

                        <span style="display:inline-block; background:rgba(255,255,255,.95); color:#ff6a00; font-size:12px; font-weight:900; padding:7px 11px; border-radius:999px;">
                          <?php echo $esc($s['badge']); ?>
                        </span>
                      </td>
                    </tr>
                  </table>

                  <div style="margin-top:10px; font-family:'Poppins',Arial,Helvetica,sans-serif; color:#ffffff;">

                    <div style="font-size:26px; font-weight:900; line-height:1.18;">
                      <?php echo $esc($s['title']); ?> <span style="font-size:22px;"><?php echo $esc($s['icon']); ?></span>
                    </div>
                    <div style="margin-top:6px; font-size:14px; font-weight:400; line-height:1.6; opacity:.94;">
                      <?php echo $esc($s['desc']); ?>
                    </div>
                  </div>
                </td>
              </tr>

              <tr>
                <td style="padding:18px 18px 20px 18px;">

                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                      <td style="padding:2px 0 10px 0;">
                        <?php
                            $step = (int) ($s['step'] ?? 2);
                            $timelineSteps = [
                                1 => ['label' => 'Kargoya verildi', 'on' => $step >= 1, 'active' => $step === 1, 'ico' => '📦'],
                                2 => ['label' => 'Yolda', 'on' => $step >= 2, 'active' => $step === 2, 'ico' => '🚚'],
                                3 => ['label' => 'Dağıtımda', 'on' => $step >= 3, 'active' => $step === 3, 'ico' => '🛵'],
                                4 => ['label' => 'Teslim edildi', 'on' => $step >= 4, 'active' => $step === 4, 'ico' => '✅'],
                            ];
                        ?>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" class="steps-desktop">
                          <tr>
                            <?php foreach ($timelineSteps as $st): ?>
                              <?php
                                  $bg = $st['on'] ? '#16a34a' : '#eef2f7';
                                  $fg = $st['on'] ? '#ffffff' : '#6b7280';

                                  $ring = $st['active'] ? 'box-shadow:0 0 0 5px rgba(22,163,74,.16);' : '';
                              ?>
                              <td align="center" width="25%" style="padding:0;">
                                <div style="width:46px; height:46px; border-radius:999px; background:<?php echo $bg; ?>; color:<?php echo $fg; ?>; line-height:46px; text-align:center; font-size:18px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-weight:700; <?php echo $ring; ?>">

                                  <?php echo $esc($st['ico']); ?>
                                </div>
                              </td>
                            <?php endforeach; ?>
                          </tr>
                        </table>

                        <?php
                            $timelineStepsList = array_values($timelineSteps);
                        ?>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" class="steps-mobile">
                          <tr>
                            <?php for ($i = 0; $i < 2; $i++): ?>
                              <?php
                                  $st = $timelineStepsList[$i];
                                  $bg = $st['on'] ? '#16a34a' : '#eef2f7';
                                  $fg = $st['on'] ? '#ffffff' : '#6b7280';
                                  $ring = $st['active'] ? 'box-shadow:0 0 0 5px rgba(22,163,74,.16);' : '';
                                  $labelColor = $st['active'] ? '#16a34a' : ($st['on'] ? '#111827' : '#9ca3af');
                                  $labelWeight = $st['active'] ? '900' : '700';
                              ?>
                              <td align="center" width="50%" style="padding:4px 0;">
                                <div style="width:46px; height:46px; border-radius:999px; background:<?php echo $bg; ?>; color:<?php echo $fg; ?>; line-height:46px; text-align:center; font-size:18px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-weight:700; <?php echo $ring; ?>">
                                  <?php echo $esc($st['ico']); ?>
                                </div>
                                <div style="margin-top:8px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; font-weight:<?php echo $labelWeight; ?>; color:<?php echo $labelColor; ?>;">
                                  <?php echo $esc($st['label']); ?>
                                </div>
                              </td>
                            <?php endfor; ?>
                          </tr>
                          <tr>
                            <?php for ($i = 2; $i < 4; $i++): ?>
                              <?php
                                  $st = $timelineStepsList[$i];
                                  $bg = $st['on'] ? '#16a34a' : '#eef2f7';
                                  $fg = $st['on'] ? '#ffffff' : '#6b7280';
                                  $ring = $st['active'] ? 'box-shadow:0 0 0 5px rgba(22,163,74,.16);' : '';
                                  $labelColor = $st['active'] ? '#16a34a' : ($st['on'] ? '#111827' : '#9ca3af');
                                  $labelWeight = $st['active'] ? '900' : '700';
                              ?>
                              <td align="center" width="50%" style="padding:4px 0;">
                                <div style="width:46px; height:46px; border-radius:999px; background:<?php echo $bg; ?>; color:<?php echo $fg; ?>; line-height:46px; text-align:center; font-size:18px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-weight:700; <?php echo $ring; ?>">
                                  <?php echo $esc($st['ico']); ?>
                                </div>
                                <div style="margin-top:8px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; font-weight:<?php echo $labelWeight; ?>; color:<?php echo $labelColor; ?>;">
                                  <?php echo $esc($st['label']); ?>
                                </div>
                              </td>
                            <?php endfor; ?>
                          </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" class="steps-desktop" style="margin-top:10px;">
                          <tr>
                            <td width="12.5%"></td>
                            <td width="75%" style="height:8px; background:#eef2f7; border-radius:999px; overflow:hidden;">
                              <div style="height:8px; width:<?php echo $esc($fill); ?>; background:#16a34a; border-radius:999px;"></div>
                            </td>
                            <td width="12.5%"></td>
                          </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" class="steps-desktop" style="margin-top:10px;">
                          <tr>
                            <?php foreach ($timelineSteps as $st): ?>
                              <?php
                                  $labelColor = $st['active'] ? '#16a34a' : ($st['on'] ? '#111827' : '#9ca3af');
                                  $labelWeight = $st['active'] ? '900' : '700';
                              ?>
                              <td align="center" width="25%" style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; font-weight:<?php echo $labelWeight; ?>; color:<?php echo $labelColor; ?>;">

                                <?php echo $esc($st['label']); ?>
                              </td>
                            <?php endforeach; ?>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>

                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:14px;">
                    <tr>
                      <td style="background:#ecfdf5; border:1px solid #bbf7d0; border-radius:14px; padding:12px 12px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                          <tr>
                            <td width="36" valign="top" style="padding-top:2px;">
                              <div style="width:28px; height:28px; border-radius:10px; background:#16a34a; color:#ffffff; line-height:28px; text-align:center; font-size:16px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-weight:700;">✓</div>

                            </td>
                            <td valign="top">
                              <div style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:14px; font-weight:600; color:#0f5132; line-height:1.3;">

                                <?php echo $esc($s['call_title']); ?>
                              </div>
                              <div style="margin-top:4px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:13px; font-weight:400; color:#14532d; line-height:1.65;">

                                <?php echo $esc($s['call_desc']); ?>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>

                  <div style="height:16px; line-height:16px; font-size:16px;">&nbsp;</div>

                  <!-- KARGO BİLGİLERİ -->
                  <?php if ($statusKey !== \Modules\Order\Entities\Order::COMPLETED): ?>
                    <div style="height:14px;line-height:14px;font-size:14px;">&nbsp;</div>
                    <div style="font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:16px;font-weight:600;color:#111827;letter-spacing:.1px;text-align:center;">Kargo Bilgileri</div>
                    <div style="height:10px;line-height:10px;font-size:10px;">&nbsp;</div>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 6px 16px rgba(17,24,39,.05);">
                      <tr>
                        <td style="padding:14px 14px;">
                          <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                              <td valign="top" style="padding-right:10px;">
                                <div style="font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:12px;font-weight:600;color:#6b7280;line-height:1.4;">Kargo Firması</div>
                                <div style="margin-top:4px;font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:13px;font-weight:600;color:#111827;line-height:1.45;"><?php echo $esc($carrierName !== '' ? $carrierName : '—'); ?></div>

                                <div style="height:10px;line-height:10px;font-size:10px;">&nbsp;</div>

                                <div style="font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:12px;font-weight:600;color:#6b7280;line-height:1.4;">Takip Numarası</div>
                                <div style="margin-top:4px;font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:13px;font-weight:600;color:#111827;line-height:1.45;"><?php echo $esc($trackingNo !== '' ? $trackingNo : '—'); ?></div>
                              </td>

                              <td align="right" valign="top" style="white-space:nowrap;">
                                <?php if ($trackingUrl): ?>
                                  <a href="<?php echo $esc($trackingUrl); ?>" style="display:inline-block;background:#111827;color:#ffffff;text-decoration:none;font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:12px;font-weight:600;padding:10px 12px;border-radius:12px;">Kargo Takibi</a>
                                <?php endif; ?>
                              </td>
                            </tr>
                          </table>

                          <?php if (!$trackingUrl && $trackingNo !== ''): ?>
                            <div style="margin-top:10px;font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:12px;color:#6b7280;line-height:1.6;">Takip linki henüz oluşturulmadı. Kargo firması sisteminde takip numarasıyla sorgulayabilirsiniz.</div>
                          <?php endif; ?>
                        </td>
                      </tr>
                    </table>
                  <?php endif; ?>

                  <div style="height:16px; line-height:16px; font-size:16px;">&nbsp;</div>

                  <div style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:16px; font-weight:600; color:#111827;letter-spacing:.1px; text-align:center;">Sipariş Özeti</div>

                  <div style="height:10px; line-height:10px; font-size:10px;">&nbsp;</div>

                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 6px 16px rgba(17,24,39,.05);">
                    <tr>
                      <td style="padding:14px 14px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                          <?php if ($orderNo !== ''): ?>
                            <tr>
                              <td style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; font-weight:600; color:#6b7280; padding:8px 0; border-bottom:1px solid #f1f5f9;">Sipariş No</td>
                              <td align="right" style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:13px; font-weight:600; color:#111827; padding:8px 0; border-bottom:1px solid #f1f5f9;">#<?php echo $esc($orderNo); ?></td>

                            </tr>
                          <?php endif; ?>
                          <?php if ($paymentMethod !== ''): ?>
                            <tr>
                              <td style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; font-weight:600; color:#6b7280; padding:8px 0; border-bottom:1px solid #f1f5f9;">Ödeme Yöntemi</td>
                              <td align="right" style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:13px; font-weight:600; color:#111827; padding:8px 0; border-bottom:1px solid #f1f5f9;"><?php echo $esc($paymentMethod); ?></td>

                            </tr>
                          <?php endif; ?>
                          <?php $total = $fmtMoney(data_get($order, 'total')); ?>
                          <?php if ($total): ?>
                            <tr>
                              <td style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; font-weight:600; color:#6b7280; padding:8px 0;">Toplam Tutar</td>
                              <td align="right" style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:14px; font-weight:700; color:#111827; padding:8px 0;"><?php echo $esc($total); ?></td>

                            </tr>
                          <?php endif; ?>
                        </table>
                      </td>
                    </tr>
                  </table>

                  <div style="height:16px; line-height:16px; font-size:16px;">&nbsp;</div>

                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                      <td valign="top" width="50%" class="stack-column" style="padding-right:8px;">
                        <div style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:14px; font-weight:600; color:#111827; margin-bottom:10px; text-align:center;">Teslimat Adresi</div>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 6px 16px rgba(17,24,39,.05);">
                          <tr>
                            <td style="padding:14px;">
                              <div style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:13px; font-weight:600; color:#111827; line-height:1.45;">

                                <?php echo $esc(trim((string) $getAddr($shippingSnapshot, $shippingAddress, 'first_name', '-'))); ?> <?php echo $esc(trim((string) $getAddr($shippingSnapshot, $shippingAddress, 'last_name', ''))); ?>
                              </div>
                              <?php $shipPhone = $getAddr($shippingSnapshot, $shippingAddress, 'phone', data_get($order, 'customer_phone', '')); ?>
                              <?php $shipLine = $getAddr($shippingSnapshot, $shippingAddress, 'address_line', $getAddr($shippingSnapshot, $shippingAddress, 'address_1', '')); ?>
                              <?php
                                  $shipDistrict = $getAddr($shippingSnapshot, $shippingAddress, 'district', $getAddr($shippingSnapshot, $shippingAddress, 'district_title', null));
                                  $shipCity = $getAddr($shippingSnapshot, $shippingAddress, 'city', $getAddr($shippingSnapshot, $shippingAddress, 'city_title', null));
                              ?>
                              <div style="margin-top:6px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:13px; color:#374151; line-height:1.65;">

                                <?php if ($shipPhone): ?><?php echo $esc($shipPhone); ?><br><?php endif; ?>
                                <?php if ($shipLine): ?><?php echo $esc($shipLine); ?><br><?php endif; ?>
                                <?php echo $esc(trim((string) $shipDistrict)); ?><?php echo ($shipDistrict && $shipCity) ? ' ' : ''; ?><?php echo $esc(trim((string) $shipCity)); ?>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>

                      <td valign="top" width="50%" class="stack-column" style="padding-left:8px;">
                        <div style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:14px; font-weight:600; color:#111827; margin-bottom:10px; text-align:center;">Fatura Bilgileri</div>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 6px 16px rgba(17,24,39,.05);">
                          <tr>
                            <td style="padding:14px;">
                              <?php
                                  $billFirst = $getAddr($billingSnapshot, $billingAddress, 'first_name', null);
                                  $billLast = $getAddr($billingSnapshot, $billingAddress, 'last_name', null);
                                  $billCompany = $getAddr($billingSnapshot, $billingAddress, 'company_name', $getAddr($billingSnapshot, $billingAddress, 'invoice_title', null));
                                  $billTaxNo = $getAddr($billingSnapshot, $billingAddress, 'tax_number', $getAddr($billingSnapshot, $billingAddress, 'invoice_tax_number', null));
                                  $billTaxOffice = $getAddr($billingSnapshot, $billingAddress, 'tax_office', $getAddr($billingSnapshot, $billingAddress, 'invoice_tax_office', null));
                                  $billLine = $getAddr($billingSnapshot, $billingAddress, 'address_line', $getAddr($billingSnapshot, $billingAddress, 'address_1', null));
                                  $billDistrict = $getAddr($billingSnapshot, $billingAddress, 'district', $getAddr($billingSnapshot, $billingAddress, 'district_title', null));
                                  $billCity = $getAddr($billingSnapshot, $billingAddress, 'city', $getAddr($billingSnapshot, $billingAddress, 'city_title', null));
                                  $billEmail = $getAddr($billingSnapshot, $billingAddress, 'billing_email', data_get($order, 'customer_email', null));
                              ?>
                              <div style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:13px; font-weight:600; color:#111827; line-height:1.45;">

                                <?php echo $esc(trim((string) $billFirst)); ?> <?php echo $esc(trim((string) $billLast)); ?>
                              </div>

                              <?php if ($billCompany): ?>
                                <div style="margin-top:8px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; line-height:1.5;">

                                  <span style="font-weight:600; color:#6b7280;">Firma Adı</span>
                                  <span style="font-weight:600; color:#111827;">: <?php echo $esc($billCompany); ?></span>
                                </div>
                              <?php endif; ?>
                              <?php if ($billTaxNo): ?>
                                <div style="margin-top:6px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; line-height:1.5;">

                                  <span style="font-weight:600; color:#6b7280;">Vergi No</span>
                                  <span style="font-weight:600; color:#111827;">: <?php echo $esc($billTaxNo); ?></span>
                                </div>
                              <?php endif; ?>
                              <?php if ($billTaxOffice): ?>
                                <div style="margin-top:6px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; line-height:1.5;">

                                  <span style="font-weight:600; color:#6b7280;">Vergi Dairesi</span>
                                  <span style="font-weight:600; color:#111827;">: <?php echo $esc($billTaxOffice); ?></span>
                                </div>
                              <?php endif; ?>

                              <div style="margin-top:6px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:13px; color:#374151; line-height:1.65;">

                                <?php if ($billLine): ?><?php echo $esc($billLine); ?><br><?php endif; ?>
                                <?php echo $esc(trim((string) $billDistrict)); ?><?php echo ($billDistrict && $billCity) ? ' ' : ''; ?><?php echo $esc(trim((string) $billCity)); ?>
                                <?php if ($billEmail): ?><br><?php echo $esc($billEmail); ?><?php endif; ?>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>

                  <div style="height:18px; line-height:18px; font-size:18px;">&nbsp;</div>
                  <div style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:14px; font-weight:600; color:#111827; margin-bottom:10px; text-align:center;">Ürünler</div>

                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 6px 16px rgba(17,24,39,.05);">
                    <tr>
                      <td style="padding:12px 14px;">
                        <?php $hasProduct = false; ?>
                        <?php foreach ($products as $product): ?>
                          <?php
                              $hasProduct = true;
                              $pName = data_get($product, 'name') ?: data_get($product, 'product_name') ?: '-';
                              $pSku = data_get($product, 'sku') ?: data_get($product, 'product_sku');
                              $pImg = data_get($product, 'product_variant.base_image.path')
                                  ?: data_get($product, 'product.base_image.path')
                                  ?: data_get($product, 'product_image_path');
                              $line = $fmtMoney(data_get($product, 'line_total'));
                              $qtyText = null;
                              try {
                                  if (is_object($product) && method_exists($product, 'getFormattedQuantityWithUnit')) {
                                      $qtyText = $product->getFormattedQuantityWithUnit();
                                  }
                              } catch (\Throwable $e) {
                                  $qtyText = null;
                              }
                          ?>
                          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-bottom:1px solid #f1f5f9;">
                            <tr>
                              <td style="padding:12px 0;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <td width="76" valign="top" style="padding-right:12px;">
                                      <?php if ($pImg): ?>
                                        <img src="<?php echo $esc($pImg); ?>" width="64" height="64" style="display:block;border-radius:12px;border:1px solid #e5e7eb;object-fit:cover;" alt="<?php echo $esc($pName); ?>">
                                      <?php endif; ?>
                                    </td>

                                    <td valign="top" style="padding-right:10px;">
                                      <div style="font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:13px;font-weight:600;color:#111827;line-height:1.35;">
                                        <?php if (data_get($product, 'is_upsell')): ?>
                                          <div style="margin-bottom: 4px;">
                                            <span style="background-color: #fef3c7; color: #92400e; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                                              <?php echo $esc(trans('storefront::upsell.offer_badge')); ?>
                                            </span>
                                          </div>
                                        <?php endif; ?>
                                        <?php echo $esc($pName); ?>
                                      </div>

                                      <div style="margin-top:6px;font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;color:#374151;">

                                        <span style="color:#6b7280;font-weight:600;">Stok Kodu:</span>
                                        <span style="color:#111827;font-weight:600;"> <?php echo $esc($pSku ?: '—'); ?></span>
                                      </div>

                                      <?php if ($qtyText): ?>
                                        <div style="margin-top:4px;font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;color:#6b7280;font-weight:600;">

                                          <?php echo $esc($qtyText); ?>
                                        </div>
                                      <?php endif; ?>
                                    </td>

                                    <td align="right" valign="top" style="white-space:nowrap;">
                                      <div style="font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:14px;font-weight:700;color:#16a34a;line-height:1.2;">
                                        <?php if (data_get($product, 'is_upsell') && ($orig = data_get($product, 'original_price'))): ?>
                                          <div style="color: #94a3b8; font-size: 11px; text-decoration: line-through; margin-bottom: 2px;">
                                            <?php echo $esc($fmtMoney($orig->multiply(data_get($product, 'qty')))); ?>
                                          </div>
                                        <?php endif; ?>
                                        <?php echo $esc($line); ?>
                                      </div>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        <?php endforeach; ?>

                        <?php if (!$hasProduct): ?>
                          <div style="font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:13px; color:#6b7280;">Ürün bulunamadı.</div>
                        <?php endif; ?>

                        <?php if ($primaryCtaUrl): ?>
                          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:6px;">
                            <tr>
                              <td>
                                <a href="<?php echo $esc($primaryCtaUrl); ?>" style="display:block;background:#ff6a00;color:#ffffff;text-decoration:none;text-align:center;font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:15px;font-weight:600;padding:14px 16px;border-radius:14px;box-shadow:0 10px 20px rgba(255,106,0,.22);">

                                  <?php echo $esc($primaryCtaText); ?>
                                </a>

                                <?php if ($trackingUrl && $secondaryCtaUrl): ?>
                                  <a href="<?php echo $esc($secondaryCtaUrl); ?>" style="display:block;margin-top:10px;background:#ffffff;color:#111827;text-decoration:none;text-align:center;font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:14px;font-weight:700;padding:12px 16px;border-radius:14px;border:1px solid #e5e7eb;">
                                    <?php echo $esc($secondaryCtaText); ?>
                                  </a>
                                <?php endif; ?>
                                <div style="margin-top:10px;font-family:'Poppins',Arial,Helvetica,sans-serif;font-size:12px;color:#6b7280;line-height:1.6;text-align:center;">

                                  Bu e-posta otomatik olarak oluşturulmuştur. Yardım için
                                  <a href="<?php echo $esc(url('/contact')); ?>" style="color:#ff6a00;text-decoration:none;font-weight:600;">Destek</a>

                                  sayfamızı ziyaret edin.
                                </div>
                              </td>
                            </tr>
                          </table>
                        <?php endif; ?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

              <tr>
                <td style="background:#0b1220; padding:18px 18px; border-top:1px solid rgba(255,255,255,.08);">
                  <div style="font-family:'Poppins',Arial,Helvetica,sans-serif; color:#ffffff; font-size:16px; font-weight:700; text-align:center;">
                    <?php echo $esc($storeName); ?>
                  </div>
                  <div style="margin-top:10px; font-family:'Poppins',Arial,Helvetica,sans-serif; font-size:12px; text-align:center;">
                    <a href="<?php echo $esc($accountUrl); ?>" style="color:rgba(255,255,255,.86); text-decoration:none; font-weight:700;">Hesabım</a>
                    <span style="color:rgba(255,255,255,.35); padding:0 10px;">|</span>
                    <a href="<?php echo $esc($orderDetailsUrl ?: $accountUrl); ?>" style="color:rgba(255,255,255,.86); text-decoration:none; font-weight:700;">Siparişlerim</a>
                    <span style="color:rgba(255,255,255,.35); padding:0 10px;">|</span>
                    <a href="<?php echo $esc($homeUrl); ?>" style="color:rgba(255,255,255,.86); text-decoration:none; font-weight:700;">Destek</a>
                  </div>
                  <div style="margin-top:12px; font-family:'Poppins',Arial,Helvetica,sans-serif; color:rgba(255,255,255,.55); font-size:12px; text-align:center;">
                    &copy; <?php echo $esc(date('Y')); ?> <?php echo $esc($storeName); ?>
                  </div>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>