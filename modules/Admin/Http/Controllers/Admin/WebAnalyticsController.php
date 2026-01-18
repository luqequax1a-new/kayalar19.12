<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Setting\Entities\Setting;

class WebAnalyticsController extends Controller
{
    public function index()
    {
        $analytics = $this->getAnalyticsData();
        
        return view('admin::analytics.index', compact('analytics'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'nullable|string',
        ]);

        $allowedKeys = [
            'facebook_pixel_id',
            'facebook_conversion_api_token',
            'google_analytics_measurement_id',
            'google_analytics_api_secret',
            'google_tag_manager_id',
            'tiktok_pixel_id',
        ];

        if (!in_array($validated['key'], $allowedKeys, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz ayar anahtarı.'
            ], 422);
        }

        $tests = [
            'facebook_pixel_id' => [
                'pattern' => '/^\d{6,20}$/',
                'message' => 'Facebook Pixel ID sayı olmalıdır.'
            ],
            'google_analytics_measurement_id' => [
                'pattern' => '/^G-[A-Z0-9]{6,20}$/i',
                'message' => 'Google Analytics ID formatı: G-...'
            ],
            'google_tag_manager_id' => [
                'pattern' => '/^GTM-[A-Z0-9]{5,15}$/i',
                'message' => 'Google Tag Manager ID formatı: GTM-...'
            ],
            'tiktok_pixel_id' => [
                'pattern' => '/^[A-Z0-9]{6,40}$/i',
                'message' => 'TikTok Pixel ID formatı geçersiz görünüyor.'
            ],
        ];

        if (isset($tests[$validated['key']]) && !empty($validated['value'])) {
            if (!preg_match($tests[$validated['key']]['pattern'], $validated['value'])) {
                return response()->json([
                    'success' => false,
                    'message' => $tests[$validated['key']]['message']
                ], 422);
            }
        }

        Setting::set($validated['key'], $validated['value']);

        return response()->json([
            'success' => true,
            'message' => 'Ayarlar başarıyla kaydedildi.'
        ]);
    }

    public function test(Request $request)
    {
        $key = $request->input('key');
        $value = $request->input('value');

        // Basit test - ID formatını kontrol et
        $tests = [
            'facebook_pixel_id' => [
                'pattern' => '/^\d{15,16}$/',
                'message' => 'Facebook Pixel ID 15-16 haneli sayı olmalıdır.'
            ],
            'google_analytics_measurement_id' => [
                'pattern' => '/^G-[A-Z0-9]{10}$/',
                'message' => 'Google Analytics ID formatı: G-XXXXXXXXXX'
            ],
            'google_tag_manager_id' => [
                'pattern' => '/^GTM-[A-Z0-9]{7}$/',
                'message' => 'Google Tag Manager ID formatı: GTM-XXXXXXX'
            ],
            'tiktok_pixel_id' => [
                'pattern' => '/^[A-Z0-9]{19}$/',
                'message' => 'TikTok Pixel ID 19 karakterli olmalıdır.'
            ],
        ];

        if (isset($tests[$key]) && !empty($value)) {
            if (!preg_match($tests[$key]['pattern'], $value)) {
                return response()->json([
                    'success' => false,
                    'message' => $tests[$key]['message']
                ], 422);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'ID formatı doğru görünüyor.'
        ]);
    }

    protected function getAnalyticsData()
    {
        return [
            [
                'key' => 'facebook_pixel_id',
                'title' => 'Facebook Pixel',
                'description' => 'Yeni müşteri kitlenizi kolayca keşfedin. Otomatik kurulan Facebook Pixel ile kampanyalarınızın performansını anlık takip edin ve dönüşüm optimizasyonunu zahmetsizce yapın.',
                'icon' => 'fab fa-facebook',
                'color' => '#1877f2',
                'value' => setting('facebook_pixel_id') ?? '',
                'placeholder' => '1482228299685069',
                'type' => 'text',
                'help_url' => 'https://business.facebook.com/events_manager',
            ],
            [
                'key' => 'facebook_conversion_api_token',
                'title' => 'Facebook Conversion API',
                'description' => 'Tarayıcı kısıtlamalarından bağımsız, en doğru veriyi Facebook\'a iletin. İçerik görüntüleme, sepete ekleme ve satın alma gibi tüm dönüşümleriniz güvende olsun, reklam bütçenizi boşa harcamayın.',
                'icon' => 'fab fa-facebook',
                'color' => '#1877f2',
                'value' => setting('facebook_conversion_api_token') ?? '',
                'placeholder' => 'EAAOMLkrflBPyxs823E37cPh51KWNZa2xAzJcgEJuFXEHL...',
                'type' => 'textarea',
                'help_url' => 'https://developers.facebook.com/docs/marketing-api/conversions-api',
            ],
            [
                'key' => 'google_analytics_measurement_id',
                'title' => 'Google Analytics Ölçüm Kimliği',
                'description' => 'E-ticaret sitenizin tüm trafik ve dönüşüm verilerini tek tıkla GA4\'a aktarın. En güncel raporlama özelliklerini sorunsuzca kullanın, kararlarınızı veriye dayandırın.',
                'icon' => 'fab fa-google',
                'color' => '#4285f4',
                'value' => setting('google_analytics_measurement_id') ?? '',
                'placeholder' => 'G-1YXV9BFHMT',
                'type' => 'text',
                'help_url' => 'https://analytics.google.com',
            ],
            [
                'key' => 'google_analytics_api_secret',
                'title' => 'Google Analytics Measurement Protocol API Gizli Anahtarı',
                'description' => 'Satın alma verilerinizi doğrudan ilkas sunucularından Google Analytics\'e gönderin. Eksik siz sunucunuz doğrudan iletişim kurduğu için en güvenilir yöntemle kullanın.',
                'icon' => 'fab fa-google',
                'color' => '#4285f4',
                'value' => setting('google_analytics_api_secret') ?? '',
                'placeholder' => 'ODgem0EGSGlmqp0_LZTA',
                'type' => 'textarea',
                'help_url' => 'https://developers.google.com/analytics/devguides/collection/protocol/ga4',
            ],
            [
                'key' => 'google_tag_manager_id',
                'title' => 'Google Tag Manager',
                'description' => 'Tüm izleme ve pazarlama etiketlerinizi tek panelden yönetin. Kod yazmaya gerek kalmadan üçüncü parti araçları kolayca ekleyin, güncelleyin ve test edin.',
                'icon' => 'fas fa-tags',
                'color' => '#4285f4',
                'value' => setting('google_tag_manager_id') ?? '',
                'placeholder' => 'GTM-5FXCW9TK',
                'type' => 'text',
                'help_url' => 'https://tagmanager.google.com',
            ],
            [
                'key' => 'tiktok_pixel_id',
                'title' => 'TikTok Piksel Kimliği',
                'description' => 'TikTok reklamlarınızın performansını doğru şekilde ölçün. Dönüşüm verilerini otomatik toplayarak hedef kitle optimizasyonunu kolaylaştırın ve reklam bütçenizi en verimli şekilde kullanın.',
                'icon' => 'fab fa-tiktok',
                'color' => '#000000',
                'value' => setting('tiktok_pixel_id') ?? '',
                'placeholder' => 'XXXXXXXXXXXXXXXXX',
                'type' => 'text',
                'help_url' => 'https://ads.tiktok.com',
            ],
        ];
    }
}
