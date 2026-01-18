@extends('admin::layout')

@section('title', 'Web Veri İzleme ve Analiz Araçları')

@section('content_header')
    <h3 class="mb-4">
        <i class="fa fa-chart-line"></i> Web Veri İzleme ve Analiz Araçları
    </h3>
@endsection

@section('content')
    <div class="web-analytics-container">
        <div class="analytics-header mb-4">
            <p class="text-muted">
                E-ticaret sitenizin performansını ölçmek ve optimize etmek için kullanabileceğiniz analiz araçlarını buradan yönetebilirsiniz.
                Her bir aracı aktif etmek için ilgili kimlik bilgilerini girin ve kaydedin.
            </p>
        </div>

        <div class="analytics-cards">
            @foreach($analytics as $tool)
            <div class="analytics-card" data-key="{{ $tool['key'] }}">
                <div class="card-icon" style="background-color: {{ $tool['color'] }}15;">
                    <i class="{{ $tool['icon'] }}" style="color: {{ $tool['color'] }};"></i>
                </div>
                
                <div class="card-content">
                    <div class="card-header-section">
                        <h5 class="card-title">{{ $tool['title'] }}</h5>
                        <div class="card-actions">
                            @if(!empty($tool['value']))
                                <span class="badge badge-success">
                                    <i class="fa fa-check-circle"></i> Aktif
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    <i class="fa fa-times-circle"></i> Pasif
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <p class="card-description">{{ $tool['description'] }}</p>
                    
                    @if(!empty($tool['value']))
                        <div class="card-value">
                            <span class="value-label">{{ $tool['key'] === 'facebook_conversion_api_token' || $tool['key'] === 'google_analytics_api_secret' ? 'Token' : 'Kimlik' }}:</span>
                            <code class="value-display">{{ \Illuminate\Support\Str::limit($tool['value'], 30) }}</code>
                        </div>
                    @endif
                    
                    <div class="card-footer-actions">
                        <button class="btn btn-sm btn-primary edit-btn" data-tool='@json($tool)'>
                            <i class="fa fa-edit"></i> {{ !empty($tool['value']) ? 'Düzenle' : 'Bağlantıyı Kur' }}
                        </button>
                        
                        @if(!empty($tool['help_url']))
                            <a href="{{ $tool['help_url'] }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fa fa-external-link-alt"></i> Yardım
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="modal-icon"></i>
                        <span class="modal-title-text"></span> Bağlantısını Düzenle
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="analyticsForm">
                        <input type="hidden" id="tool_key" name="key">
                        
                        <div class="form-group">
                            <label class="form-label">
                                <span class="label-text"></span>
                            </label>
                            <input type="text" class="form-control" id="tool_value" name="value" placeholder="">
                            <textarea class="form-control" id="tool_value_textarea" name="value" rows="3" placeholder="" style="display:none;"></textarea>
                            <small class="form-text text-muted mt-2" id="tool_format_hint" style="display:none;"></small>
                            <small class="form-text text-muted" id="tool_help_hint" style="display:none;"></small>
                            <small class="form-text text-muted tool-description mt-2"></small>
                            <button class="btn btn-sm btn-outline-secondary mt-2" type="button" id="clearBtn">
                                <i class="fa fa-times"></i> Temizle
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
                    <button type="button" class="btn btn-primary" id="saveBtn">
                        <i class="fa fa-save"></i> Güncelle
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
/* Fix sidebar menu text wrapping */
.sidebar-menu > li > a,
.treeview-menu > li > a {
    white-space: normal !important;
    word-wrap: break-word !important;
    line-height: 1.4 !important;
}

.web-analytics-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 0;
}

.analytics-header {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #0071e3;
    margin-bottom: 30px;
}

.analytics-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
    gap: 20px;
}

.analytics-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    gap: 20px;
    transition: all 0.3s ease;
}

.analytics-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.card-icon {
    width: 60px;
    height: 60px;
    min-width: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.card-icon i {
    font-size: 32px !important;
    line-height: 1;
    display: block;
}

.card-content {
    flex: 1;
}

.card-header-section {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    color: #1d1d1f;
}

.card-description {
    font-size: 14px;
    color: #6e6e73;
    line-height: 1.5;
    margin-bottom: 16px;
}

.card-value {
    background: #f5f5f7;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 16px;
}

.value-label {
    font-size: 12px;
    color: #86868b;
    display: block;
    margin-bottom: 4px;
}

.value-display {
    font-size: 13px;
    color: #1d1d1f;
    font-family: 'Monaco', 'Menlo', monospace;
}

.card-footer-actions {
    display: flex;
    gap: 10px;
}

.badge {
    font-size: 12px;
    padding: 6px 12px;
    font-weight: 500;
}

.badge-success {
    background-color: #34c759;
}

.badge-secondary {
    background-color: #8e8e93;
}

.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-dialog {
    display: flex;
    align-items: center;
    min-height: calc(100% - 3.5rem);
}

.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.modal-header {
    border-bottom: 1px solid #e0e0e0;
    padding: 20px 24px;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 600;
}

.modal-title .modal-icon {
    font-size: 24px !important;
    line-height: 1;
}

.modal-body {
    padding: 24px;
}

.form-label {
    font-weight: 600;
    color: #1d1d1f;
    margin-bottom: 8px;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #d2d2d7;
    padding: 12px 16px;
    font-size: 15px;
    width: 100%;
}

.form-control:focus {
    border-color: #0071e3;
    box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
    outline: none;
}

.form-group {
    margin-bottom: 0;
}

.form-label {
    font-weight: 600;
    color: #1d1d1f;
    margin-bottom: 12px;
    display: block;
    font-size: 15px;
}

.tool-description {
    display: block;
    color: #6e6e73;
    font-size: 13px;
    line-height: 1.5;
}

.btn {
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary {
    background-color: #0071e3;
    border-color: #0071e3;
}

.btn-primary:hover {
    background-color: #0077ed;
    border-color: #0077ed;
}

@media (max-width: 768px) {
    .analytics-cards {
        grid-template-columns: 1fr;
    }
    
    .analytics-card {
        flex-direction: column;
    }
    
    .card-footer-actions {
        flex-direction: column;
    }
    
    .card-footer-actions .btn {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const validators = {
        facebook_pixel_id: {
            pattern: /^\d{6,20}$/,
            format: 'Sadece sayı (6-20 hane)',
        },
        google_analytics_measurement_id: {
            pattern: /^G-[A-Z0-9]{6,20}$/i,
            format: 'G- ile başlar (örn: G-XXXX...)',
        },
        google_tag_manager_id: {
            pattern: /^GTM-[A-Z0-9]{5,15}$/i,
            format: 'GTM- ile başlar (örn: GTM-XXXX...)',
        },
        tiktok_pixel_id: {
            pattern: /^[A-Z0-9]{6,40}$/i,
            format: 'Harf/sayı (6-40 karakter)',
        },
    };

    // Edit button click
    $('.edit-btn').on('click', function() {
        const tool = $(this).data('tool');
        
        $('#tool_key').val(tool.key);

        const isTextarea = tool.type === 'textarea';

        $('#tool_value').toggle(!isTextarea);
        $('#tool_value_textarea').toggle(isTextarea);

        const $input = isTextarea ? $('#tool_value_textarea') : $('#tool_value');
        $input.val(tool.value || '');
        $input.attr('placeholder', tool.placeholder);

        const hint = validators[tool.key];
        if (hint) {
            $('#tool_format_hint')
                .text('Beklenen format: ' + hint.format + (tool.placeholder ? ' | Örnek: ' + tool.placeholder : ''))
                .show();
        } else {
            $('#tool_format_hint').hide().text('');
        }

        if (tool.help_url) {
            $('#tool_help_hint')
                .html('Nereden alınır: <a href="' + tool.help_url + '" target="_blank">' + tool.help_url + '</a>')
                .show();
        } else {
            $('#tool_help_hint').hide().text('');
        }
        
        // Set modal icon with proper classes
        const iconClass = tool.icon.replace('fas', 'fa').replace('fab', 'fa');
        $('.modal-icon').attr('class', 'modal-icon ' + iconClass);
        $('.modal-icon').css('color', tool.color);
        
        $('.modal-title-text').text(tool.title);
        $('.label-text').text(tool.key.includes('token') || tool.key.includes('secret') ? 'Token / API Secret' : 'Kimlik (ID)');
        $('.tool-description').text(tool.description);
        
        $('#editModal').modal('show');
    });
    
    // Clear button
    $('#clearBtn').on('click', function() {
        $('#tool_value').val('');
        $('#tool_value_textarea').val('');
    });
    
    // Save button
    $('#saveBtn').on('click', function() {
        const key = $('#tool_key').val();
        const value = $('#tool_value_textarea').is(':visible') ? $('#tool_value_textarea').val() : $('#tool_value').val();

        const trimmedValue = (value || '').trim();
        const clientRule = validators[key];
        if (clientRule && trimmedValue.length > 0 && !clientRule.pattern.test(trimmedValue)) {
            if (typeof window.error === 'function') {
                window.error('Geçersiz format. ' + clientRule.format);
            }
            if (typeof window.info === 'function') {
                const example = ($('#tool_value_textarea').is(':visible') ? $('#tool_value_textarea') : $('#tool_value')).attr('placeholder');
                if (example) {
                    window.info('Örnek: ' + example);
                }
            }
            return;
        }
        
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...');
        
        $.ajax({
            url: '{{ route("admin.web_analytics.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                key: key,
                value: value
            },
            success: function(response) {
                $('#editModal').modal('hide');
                
                // Show success message
                if (typeof window.success === 'function') {
                    window.success(response.message || 'Ayarlar başarıyla kaydedildi.');
                }

                if (typeof window.info === 'function') {
                    window.info('Kontrol: Mağaza ana sayfasında "Sayfa Kaynağını Görüntüle" açıp GTM/GA/Pixel scriptlerini aratabilirsiniz.');
                }
                
                // Reload page after short delay
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Bir hata oluştu.';

                if (xhr.responseJSON?.errors) {
                    const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                    if (firstKey && Array.isArray(xhr.responseJSON.errors[firstKey]) && xhr.responseJSON.errors[firstKey][0]) {
                        message = xhr.responseJSON.errors[firstKey][0];
                    }
                }
                if (typeof window.error === 'function') {
                    window.error(message);
                }

                const rule = validators[key];
                if (rule && typeof window.info === 'function') {
                    const example = ($('#tool_value_textarea').is(':visible') ? $('#tool_value_textarea') : $('#tool_value')).attr('placeholder');
                    window.info('Beklenen format: ' + rule.format + (example ? ' | Örnek: ' + example : ''));
                }
                
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Güncelle');
            }
        });
    });
});
</script>
@endpush
