<!-- Global Critical Style to prevent Flash of Unstyled Content (FOUC) -->
<style id="bulk-edit-critical-css">
    #bulk-edit-modal { display: none !important; opacity: 0; }
    #bulk-edit-modal.in, #bulk-edit-modal.show { opacity: 1; }
</style>

<div class="modal fade" id="bulk-edit-modal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content ikas-pro-modal">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title" id="bulk-edit-modal-title">Toplu Ürün Düzenleme</h4>
                    <p class="modal-subtitle">Binlerce ürünü aynı anda, saniyeler içinde hatasız bir şekilde güncelleyin.</p>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Scope Selection -->
                <div class="ikas-scope-bar">
                    <label class="ikas-scope-item">
                        <input type="radio" name="bulk_scope" value="all">
                        <div class="ikas-scope-box">
                            <i class="fa fa-globe"></i>
                            <span>Mağazadaki Tüm Ürünler</span>
                        </div>
                    </label>
                    <label class="ikas-scope-item">
                        <input type="radio" name="bulk_scope" value="selected" checked>
                        <div class="ikas-scope-box">
                            <i class="fa fa-check-square-o"></i>
                            <span id="lbl-selected-count">Seçilen 0 Ürün</span>
                        </div>
                    </label>
                </div>

                <div class="ikas-main-content">
                    <!-- Action Toolbar -->
                    <div class="ikas-action-toolbar">
                        <div class="ikas-toolbar-label">Düzenlemek İstediğiniz Alanları Seçin</div>
                        <div class="ikas-toolbar-buttons">
                            <button type="button" class="btn-add-attr" data-attr="price"><i class="fa fa-tag"></i> Fiyat</button>
                            <button type="button" class="btn-add-attr" data-attr="special_price"><i class="fa fa-percent"></i> İndirimli Fiyat</button>
                            <button type="button" class="btn-add-attr" data-attr="inventory"><i class="fa fa-archive"></i> Stok & Envanter</button>
                            <button type="button" class="btn-add-attr" data-attr="primary_category"><i class="fa fa-star"></i> Varsayılan Kategori</button>
                            <button type="button" class="btn-add-attr" data-attr="category_action"><i class="fa fa-folder-open"></i> Kategori Ekle/Çıkar</button>
                            <button type="button" class="btn-add-attr" data-attr="is_active"><i class="fa fa-eye"></i> Satış Durumu</button>
                            <button type="button" class="btn-add-attr" data-attr="brand_id"><i class="fa fa-copyright"></i> Marka</button>
                            <button type="button" class="btn-add-attr" data-attr="name"><i class="fa fa-font"></i> Ürün Adı</button>
                            <button type="button" class="btn-add-attr" data-attr="description"><i class="fa fa-align-left"></i> Açıklama</button>
                        </div>
                    </div>

                    <!-- Action List -->
                    <div id="bulk-action-list" class="ikas-action-list">
                        <div class="ikas-empty-state">
                            <i class="fa fa-plus-circle"></i>
                            <p>Yapmak istediğiniz işlemi yukarıdan seçerek başlayın.</p>
                        </div>
                    </div>

                    <!-- Global Options -->
                    <div class="ikas-global-options">
                        <label class="ikas-modern-checkbox">
                            <input type="checkbox" id="bulk-apply-to-variants" checked>
                            <span class="checkmark"></span>
                            <span class="text">Bu değişiklikleri ürünlerin tüm varyantlarına da uygula</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="ikas-btn ikas-btn-link" data-dismiss="modal">Vazgeç</button>
                <button type="button" class="ikas-btn ikas-btn-primary" id="bulk-edit-apply-btn">Değişiklikleri Uygula</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Critical Visibility Fix */
    #bulk-edit-modal { display: none; }
    #bulk-edit-modal.in { display: block !important; }

    /* Premium Ikas Design System */
    .modal-dialog, .modal-content, .ikas-pro-modal {
        overflow: visible !important;
    }

    .ikas-pro-modal {
        border: none;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.4);
        font-family: 'Inter', -apple-system, sans-serif;
        position: relative;
        animation: ikasFadeIn 0.4s ease-out;
    }

    @keyframes ikasFadeIn {
        from { opacity: 0; transform: scale(0.98); }
        to { opacity: 1; transform: scale(1); }
    }

    .ikas-pro-modal .modal-header {
        background: #fff;
        padding: 24px 32px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .ikas-pro-modal .modal-title {
        font-size: 18px;
        font-weight: 800;
        color: #111;
        margin: 0;
    }

    .ikas-pro-modal .modal-subtitle {
        font-size: 13px;
        color: #666;
        margin: 2px 0 0 0;
    }

    /* Scope Selection */
    .ikas-scope-bar {
        display: flex;
        padding: 16px 32px;
        background: #fafafa;
        gap: 12px;
        border-bottom: 1px solid #f3f4f6;
    }

    .ikas-scope-item { flex: 1; cursor: pointer; margin: 0; }
    .ikas-scope-item input { display: none; }

    .ikas-scope-box {
        background: #fff;
        border: 2px solid #eee;
        border-radius: 12px;
        padding: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 700;
        color: #444;
        font-size: 13px;
    }

    .ikas-scope-item input:checked + .ikas-scope-box {
        border-color: #000;
        background: #000;
        color: #fff;
        transform: translateY(-1px);
    }

    /* Content Area */
    .ikas-main-content { padding: 32px; position: relative; }

    /* Toolbar */
    .ikas-action-toolbar { margin-bottom: 24px; }
    .ikas-toolbar-label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #aaa; margin-bottom: 12px; }
    .ikas-toolbar-buttons { display: flex; flex-wrap: wrap; gap: 8px; }

    .btn-add-attr {
        background: #fff;
        border: 1px solid #e5e7eb;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #333;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-add-attr:hover { background: #000; color: #fff; border-color: #000; }
    .btn-add-attr i { font-size: 14px; color: inherit; }

    /* Action List */
    .ikas-action-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
    
    .ikas-empty-state {
        padding: 40px;
        text-align: center;
        background: #fafafa;
        border: 2px dashed #eee;
        border-radius: 16px;
        color: #999;
    }

    .ikas-action-row {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        display: grid;
        grid-template-columns: 180px 140px 1fr 40px;
        align-items: start; /* Fix labels to top */
        gap: 12px;
        position: relative;
        z-index: 5;
        transition: border-color 0.2s;
    }
    
    .ikas-row-label { 
        padding-top: 10px; /* Align with input */
        display: flex; 
        align-items: center; 
        gap: 8px; 
        font-weight: 800; 
        color: #111; 
        font-size: 13px;
        white-space: nowrap;
    }
    
    .ikas-row-value {
        position: relative;
        width: 100%;
        min-height: 40px;
    }
    .ikas-row-label i { width: 16px; text-align: center; }

    .ikas-select, .ikas-input {
        border: 1.5px solid #eee !important;
        border-radius: 8px !important;
        height: 40px !important;
        font-size: 14px !important;
        padding: 0 12px !important;
        transition: all 0.2s;
    }
    .ikas-select:focus, .ikas-input:focus { border-color: #000 !important; outline: none; }

    .btn-remove-row {
        background: #f3f4f6;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        color: #999;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .btn-remove-row:hover { background: #fee2e2; color: #ef4444; }

    /* Modern Checkbox */
    .ikas-modern-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-weight: 600;
        color: #555;
        font-size: 14px;
    }
    .ikas-modern-checkbox input { display: none; }
    .ikas-modern-checkbox .checkmark {
        width: 20px;
        height: 20px;
        border: 2px solid #eee;
        border-radius: 6px;
        position: relative;
        background: #fff;
    }
    .ikas-modern-checkbox input:checked + .checkmark { background: #000; border-color: #000; }
    .ikas-modern-checkbox input:checked + .checkmark::after {
        content: '✓';
        position: absolute;
        color: #fff;
        font-size: 12px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Footer Buttons */
    .ikas-btn { padding: 12px 28px; border-radius: 10px; font-weight: 800; letter-spacing: -0.01em; cursor: pointer; transition: all 0.2s; border: none; }
    .ikas-btn-primary { background: #000; color: #fff; }
    .ikas-btn-primary:active { transform: scale(0.98); }
    .ikas-btn-link { background: transparent; color: #999; }
    .ikas-btn-link:hover { color: #000; }

    /* Selectize Customizations - Expand Downward Fix */
    .selectize-dropdown.ikas-pro-cat-dropdown {
        border-radius: 8px !important;
        box-shadow: none !important;
        border: 1.5px solid #eee !important;
        border-top: none !important;
        z-index: 10 !important;
        margin-top: -2px !important;
        background: #fff !important;
        width: 100% !important;
        position: relative !important; /* Forces row to expand */
        left: 0 !important;
        top: 0 !important;
        display: block !important;
        visibility: visible !important;
    }

    .selectize-input.focus {
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border-color: #000 !important;
    }
    .selectize-dropdown .option { padding: 12px 16px !important; font-size: 14px !important; }
    .selectize-input { border-radius: 8px !important; border-color: #eee !important; min-height: 40px !important; display: flex !important; align-items: center !important; }

    /* Footer Buttons */
    .ikas-btn { padding: 12px 28px; border-radius: 10px; font-weight: 800; letter-spacing: -0.01em; cursor: pointer; transition: all 0.2s; border: none; }
    .ikas-btn-primary { background: #000; color: #fff; }
    .ikas-btn-primary:active { transform: scale(0.98); }
    .ikas-btn-link { background: transparent; color: #999; }
    .ikas-btn-link:hover { color: #000; }
</style>
