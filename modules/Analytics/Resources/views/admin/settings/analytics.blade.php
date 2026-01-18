<div class="row">
    <div class="col-md-8">
        {{-- Google Tag Manager Section --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="las la-google"></i> Google Tag Manager (Önerilen)
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="las la-info-circle"></i>
                    <strong>Önerilen Yöntem:</strong> Google Tag Manager kullanarak tüm tracking kodlarınızı tek bir yerden yönetebilirsiniz. 
                    GTM aktifse, diğer Google Analytics ayarları devre dışı kalır.
                </div>
                
                <div class="form-group">
                    <label for="google_tag_manager_id">
                        Google Tag Manager ID
                        <span class="text-muted">(GTM-XXXXXXX)</span>
                    </label>
                    {{ Form::text('google_tag_manager_id', trans('Google Tag Manager ID'), $errors, setting('google_tag_manager_id'), ['placeholder' => 'GTM-55XCW31X', 'id' => 'google_tag_manager_id']) }}
                    <small class="form-text text-muted">
                        <a href="https://tagmanager.google.com" target="_blank">Google Tag Manager</a> hesabınızdan GTM ID'nizi alın.
                    </small>
                </div>
            </div>
        </div>

        {{-- Google Analytics Section --}}
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="las la-chart-line"></i> Google Analytics 4
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="las la-exclamation-triangle"></i>
                    GTM kullanıyorsanız, bu ayarları GTM üzerinden yapmanız önerilir.
                </div>

                <div class="form-group">
                    <label for="google_analytics_measurement_id">
                        Google Analytics Measurement ID
                        <span class="text-muted">(G-XXXXXXXXXX)</span>
                    </label>
                    {{ Form::text('google_analytics_measurement_id', trans('Google Analytics Measurement ID'), $errors, setting('google_analytics_measurement_id'), ['placeholder' => 'G-1XAV5SEHMT', 'id' => 'google_analytics_measurement_id']) }}
                    <small class="form-text text-muted">
                        <a href="https://analytics.google.com" target="_blank">Google Analytics</a> hesabınızdan Measurement ID'nizi alın.
                    </small>
                </div>

                <div class="form-group">
                    <label for="google_analytics_api_secret">
                        Google Analytics API Secret
                        <span class="badge badge-info">Server-Side Tracking</span>
                    </label>
                    {{ Form::textarea('google_analytics_api_secret', trans('Google Analytics API Secret'), $errors, setting('google_analytics_api_secret'), ['rows' => 2, 'placeholder' => 'O0gw7nCFG5lCmqI0_LZ7tA', 'id' => 'google_analytics_api_secret']) }}
                    <small class="form-text text-muted">
                        Server-side event tracking için API Secret gereklidir. 
                        <a href="https://developers.google.com/analytics/devguides/collection/protocol/ga4" target="_blank">Measurement Protocol</a> dokümantasyonuna bakın.
                    </small>
                </div>
            </div>
        </div>

        {{-- Facebook Pixel Section --}}
        <div class="card mb-4">
            <div class="card-header" style="background: #1877f2; color: white;">
                <h5 class="mb-0">
                    <i class="lab la-facebook"></i> Facebook Pixel
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="facebook_pixel_id">
                        Facebook Pixel ID
                    </label>
                    {{ Form::text('facebook_pixel_id', trans('Facebook Pixel ID'), $errors, setting('facebook_pixel_id'), ['placeholder' => '1405225298985095', 'id' => 'facebook_pixel_id']) }}
                    <small class="form-text text-muted">
                        <a href="https://business.facebook.com/events_manager" target="_blank">Facebook Events Manager</a> üzerinden Pixel ID'nizi bulabilirsiniz.
                    </small>
                </div>

                <div class="form-group">
                    <label for="facebook_conversion_api_token">
                        Facebook Conversion API Token
                        <span class="badge badge-info">Server-Side Tracking</span>
                    </label>
                    {{ Form::textarea('facebook_conversion_api_token', trans('Facebook Conversion API Token'), $errors, setting('facebook_conversion_api_token'), ['rows' => 2, 'placeholder' => 'EAAOMLkrflBPyxs823E37cPh51KWNZa2xAzJcgEJuFXEHL...', 'id' => 'facebook_conversion_api_token']) }}
                    <small class="form-text text-muted">
                        iOS 14.5+ güncellemesi sonrası önemli! Server-side tracking için Conversion API token gereklidir.
                        <a href="https://developers.facebook.com/docs/marketing-api/conversions-api" target="_blank">Conversion API Dokümantasyonu</a>
                    </small>
                </div>
            </div>
        </div>

        {{-- TikTok Pixel Section --}}
        <div class="card mb-4">
            <div class="card-header" style="background: #000; color: white;">
                <h5 class="mb-0">
                    <i class="lab la-tiktok"></i> TikTok Pixel
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="tiktok_pixel_id">
                        TikTok Pixel ID
                    </label>
                    {{ Form::text('tiktok_pixel_id', trans('TikTok Pixel ID'), $errors, setting('tiktok_pixel_id'), ['placeholder' => 'XXXXXXXXXXXXXXXXX', 'id' => 'tiktok_pixel_id']) }}
                    <small class="form-text text-muted">
                        <a href="https://ads.tiktok.com/help/article?aid=10000357" target="_blank">TikTok Ads Manager</a> üzerinden Pixel ID'nizi bulabilirsiniz.
                    </small>
                </div>
            </div>
        </div>

        {{-- Test & Validation Section --}}
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="las la-check-circle"></i> Test & Doğrulama
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-secondary">
                    <h6><strong>Tracking Kodlarınızı Test Edin:</strong></h6>
                    <ul class="mb-0">
                        <li><strong>Google Tag Manager:</strong> <a href="https://tagassistant.google.com/" target="_blank">Tag Assistant</a></li>
                        <li><strong>Google Analytics:</strong> <a href="https://analytics.google.com/analytics/web/#/realtime" target="_blank">Realtime Reports</a></li>
                        <li><strong>Facebook Pixel:</strong> <a href="https://chrome.google.com/webstore/detail/facebook-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc" target="_blank">Pixel Helper Chrome Extension</a></li>
                        <li><strong>TikTok Pixel:</strong> <a href="https://chrome.google.com/webstore/detail/tiktok-pixel-helper/aelgobmabdmlfmiblddjfnjodalhidnn" target="_blank">TikTok Pixel Helper</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar Info --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="las la-info-circle"></i> Bilgi
                </h5>
            </div>
            <div class="card-body">
                <h6><strong>Otomatik Tracking Özellikleri:</strong></h6>
                <ul>
                    <li>✅ Sayfa görüntülemeleri</li>
                    <li>✅ Ürün görüntülemeleri</li>
                    <li>✅ Sepete ekleme</li>
                    <li>✅ Ödeme başlatma</li>
                    <li>✅ Satın alma</li>
                    <li>✅ Arama olayları</li>
                </ul>

                <hr>

                <h6><strong>GDPR/KVKK Uyumluluğu:</strong></h6>
                <p class="small">
                    Sistem otomatik olarak cookie consent banner gösterir ve kullanıcı izni alır.
                </p>

                <hr>

                <h6><strong>Server-Side Tracking:</strong></h6>
                <p class="small">
                    API Secret/Token girdiğinizde, sistem otomatik olarak server-side tracking'i etkinleştirir.
                    Bu, iOS 14.5+ ve ad blocker kullanıcıları için önemlidir.
                </p>

                <hr>

                <h6><strong>Performance:</strong></h6>
                <p class="small">
                    Tüm tracking scriptleri async olarak yüklenir ve sayfa hızını etkilemez.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-warning">
                <h5 class="mb-0">
                    <i class="las la-lightbulb"></i> İpuçları
                </h5>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <strong>1. GTM Kullanın:</strong> Tüm tracking kodlarınızı GTM üzerinden yönetmek en iyi pratiktir.
                </p>
                <p class="small mb-2">
                    <strong>2. Server-Side Tracking:</strong> Conversion API ve Measurement Protocol kullanarak tracking doğruluğunu artırın.
                </p>
                <p class="small mb-0">
                    <strong>3. Test Edin:</strong> Kaydetmeden önce mutlaka test araçlarıyla doğrulayın.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header h5 {
    font-size: 16px;
    font-weight: 600;
}

.badge {
    font-size: 11px;
    padding: 4px 8px;
}

.alert ul {
    padding-left: 20px;
}

.alert ul li {
    margin-bottom: 5px;
}
</style>
