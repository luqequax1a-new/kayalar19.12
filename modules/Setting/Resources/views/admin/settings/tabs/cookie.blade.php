<div class="cookie-settings-wrapper">
    <div class="row">
        <div class="col-md-8">
            {{-- Cookie Bar Toggle --}}
            <div class="settings-card">
                <div class="card-header-clean card-header-with-toggle">
                    <div class="header-left">
                        <h4 class="card-title-clean">
                            <i class="fa fa-toggle-on"></i>
                            Çerez Çubuğu
                        </h4>
                        <p class="card-subtitle">Sitede çerez bilgilendirme banner'ını göster veya gizle</p>
                    </div>
                    <div class="header-right">
                        <label class="toggle-switch">
                            <input type="checkbox" name="cookie_bar_enabled" value="1" {{ old('cookie_bar_enabled', setting('cookie_bar_enabled')) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Banner Customization --}}
            <div class="settings-card mt-4">
                <div class="card-header-clean">
                    <h4 class="card-title-clean">
                        <i class="fa fa-paint-brush"></i>
                        Banner Özelleştirme
                    </h4>
                    <p class="card-subtitle">Çerez banner'ının başlık ve içeriğini düzenleyin</p>
                </div>
                <div class="card-body-clean">
                    {{ Form::text('cookie_consent_title', trans('setting::attributes.cookie_consent_title'), $errors, $settings, ['required' => false]) }}
                    {{ Form::wysiwyg('cookie_consent_message', trans('setting::attributes.cookie_consent_message'), $errors, $settings, ['labelCol' => 2, 'required' => false]) }}
                    
                    <div class="help-box">
                        <i class="fa fa-info-circle"></i>
                        <strong>İpucu:</strong> Editörü kullanarak metin formatı verebilir, link ekleyebilir ve içeriği zenginleştirebilirsiniz.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Info Card --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fa fa-lightbulb"></i>
                    <h5>Bilgi</h5>
                </div>
                <div class="info-card-body">
                    <h6><strong>KVKK Uyumluluğu</strong></h6>
                    <p class="small">Çerez banner'ı, KVKK ve GDPR düzenlemelerine uyum sağlamanıza yardımcı olur.</p>
                    
                    <hr>
                    
                    <h6><strong>Öneriler</strong></h6>
                    <ul class="small">
                        <li>Banner başlığını kısa ve net tutun</li>
                        <li>Aydınlatma metninde çerez politikası linkini ekleyin</li>
                        <li>Kullanıcı dostu bir dil kullanın</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cookie-settings-wrapper {
    padding: 20px 0;
}

.settings-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s;
}

.settings-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.card-header-clean {
    padding: 24px 24px 16px;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
}

.card-header-with-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
}

.header-left {
    flex: 1;
}

.header-right {
    flex-shrink: 0;
    margin-left: 20px;
}

.card-title-clean {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-title-clean i {
    color: #6366f1;
    font-size: 20px;
}

.card-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
}

.card-body-clean {
    padding: 24px;
}

.help-box {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 16px;
    border-radius: 8px;
    margin-top: 20px;
    font-size: 14px;
    color: #1e40af;
    line-height: 1.6;
}

.help-box i {
    margin-right: 8px;
    color: #3b82f6;
}

.info-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    position: sticky;
    top: 20px;
}

.info-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.info-card-header i {
    font-size: 24px;
}

.info-card-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.info-card-body {
    padding: 20px;
}

.info-card-body h6 {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 8px;
}

.info-card-body p.small {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 0;
}

.info-card-body ul.small {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.8;
    padding-left: 20px;
    margin-bottom: 0;
}

.info-card-body hr {
    margin: 16px 0;
    border: 0;
    border-top: 1px solid #e5e7eb;
}

/* Form styling improvements */
.cookie-settings-wrapper .form-group {
    margin-bottom: 20px;
}

.cookie-settings-wrapper .control-label {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.cookie-settings-wrapper .form-control {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.2s;
}

.cookie-settings-wrapper .form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    outline: none;
}

/* Checkbox styling */
.cookie-settings-wrapper .checkbox label {
    font-size: 14px;
    color: #374151;
    font-weight: 500;
}

/* Modern Toggle Switch */
.toggle-switch-wrapper {
    display: flex;
    align-items: center;
    gap: 16px;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    margin: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: 0.3s;
    border-radius: 28px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #6366f1;
}

.toggle-switch input:focus + .toggle-slider {
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

.toggle-label {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

@media (max-width: 768px) {
    .info-card {
        position: static;
        margin-top: 20px;
    }
}
</style>
