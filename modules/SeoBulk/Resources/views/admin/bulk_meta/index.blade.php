@extends('admin::layout')

@section('title', 'Bulk Meta Manager')

@section('content')
    <style>
        .token-chip { display:inline-block; padding:6px 10px; margin:4px; background:#eef2f7; border:1px dashed #b0c4de; border-radius:6px; cursor:pointer; font-size:12px; }
        .box-title { font-weight:600; }
    </style>
    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <div class="box-header"><h3 class="box-title">Kategori Meta</h3></div>
                <div class="box-body">
                    <form id="category-form">
                        <div class="form-group">
                            <label>Kategoriler</label>
                            <div id="cat-tree" class="category-tree"></div>
                        </div>
                        <div class="form-group">
                            <label>Meta title</label>
                            <input name="title_template" class="form-control" placeholder="%category.name% %separator% Uygun Fiyat ve Hızlı Kargo">
                            <div>
                                <span class="token-chip" data-token="%shop.name%" data-target="#category-form [name=title_template]">Shop name</span>
                                <span class="token-chip" data-token="%separator%" data-target="#category-form [name=title_template]">Separator</span>
                                <span class="token-chip" data-token="%category.name%" data-target="#category-form [name=title_template]">Category name</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Meta description</label>
                            <textarea name="description_template" class="form-control" rows="3" placeholder="%category.description%"></textarea>
                            <div>
                                <span class="token-chip" data-token="%shop.name%" data-target="#category-form [name=description_template]">Shop name</span>
                                <span class="token-chip" data-token="%separator%" data-target="#category-form [name=description_template]">Separator</span>
                                <span class="token-chip" data-token="%category.name%" data-target="#category-form [name=description_template]">Category name</span>
                                <span class="token-chip" data-token="%category.description%" data-target="#category-form [name=description_template]">Açıklama</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" id="cat-execute">Save</button>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header"><h3 class="box-title">Ürün Meta</h3></div>
                <div class="box-body">
                    <form id="product-form">
                        <div class="form-group">
                            <label>Kategoriler</label>
                            <div id="prod-tree" class="category-tree"></div>
                            <small style="display:block; margin-top:10px">Seçilen kategorilere ait ürünler güncellenecek.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Meta title</label>
                            <input name="title_template" class="form-control" placeholder="%product.name% %separator% Aynı Gün Kargo, Kapıda Ödeme">
                            <div>
                                <span class="token-chip" data-token="%shop.name%" data-target="#product-form [name=title_template]">Shop name</span>
                                <span class="token-chip" data-token="%separator%" data-target="#product-form [name=title_template]">Separator</span>
                                <span class="token-chip" data-token="%product.name%" data-target="#product-form [name=title_template]">Ürün adı</span>
                                <span class="token-chip" data-token="%product.discount_price%" data-target="#product-form [name=title_template]">İndirimli fiyat</span>
                                <span class="token-chip" data-token="%brand%" data-target="#product-form [name=title_template]">Marka</span>
                                <span class="token-chip" data-token="%category.name%" data-target="#product-form [name=title_template]">Kategori</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Meta description</label>
                            <textarea name="description_template" class="form-control" rows="3" placeholder="%product.summary%"></textarea>
                            <div>
                                <span class="token-chip" data-token="%shop.name%" data-target="#product-form [name=description_template]">Shop name</span>
                                <span class="token-chip" data-token="%separator%" data-target="#product-form [name=description_template]">Separator</span>
                                <span class="token-chip" data-token="%product.summary%" data-target="#product-form [name=description_template]">Summary</span>
                                <span class="token-chip" data-token="%product.description%" data-target="#product-form [name=description_template]">Açıklama</span>
                                <span class="token-chip" data-token="%product.sku%" data-target="#product-form [name=description_template]">Stok Kodu</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" id="prod-execute">Save</button>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jstree@3.3.12/dist/themes/default/style.min.css">
@endpush

@push('scripts')
<script>
// Ensure jsTree availability (fallback to CDN if missing)
function ensureJsTree(callback){
    if (window.$ && $.fn && $.fn.jstree) { callback(); return; }
    function load(src, onload){ const s=document.createElement('script'); s.src=src; s.onload=onload; document.head.appendChild(s); }
    load('https://cdn.jsdelivr.net/npm/jstree@3.3.12/dist/jstree.min.js', callback);
}
function initTree(elId) {
    const el = $(elId);
    el.jstree({
        core: { data: { url: '{{ route('admin.seo.categories.tree') }}' }, check_callback: true },
        plugins: ['checkbox']
    });
    el.on('loaded.jstree', ()=> el.jstree('open_all'));
    return el;
}
let catTree, prodTree;
ensureJsTree(()=>{
    catTree = initTree('#cat-tree');
    prodTree = initTree('#prod-tree');
});

function collectForm(selector) {
    const form = document.querySelector(selector);
    const fd = new FormData(form);
    const obj = {};
    fd.forEach((v,k)=>{ if (obj[k]) { if (!Array.isArray(obj[k])) obj[k] = [obj[k]]; obj[k].push(v); } else { obj[k]=v; } });
    return obj;
}
function toParams(obj){
    const p = new URLSearchParams();
    Object.entries(obj).forEach(([k,v])=>{
        if (Array.isArray(v)) v.forEach(val=>p.append(k, val));
        else if (v !== undefined && v !== null) p.append(k, v);
    });
    return p;
}
function insertToken(targetSel, token) {
    const el = document.querySelector(targetSel);
    if (!el) return;
    const start = el.selectionStart ?? el.value.length;
    const end = el.selectionEnd ?? el.value.length;
    el.value = el.value.substring(0,start) + token + el.value.substring(end);
    el.focus();
}
document.querySelectorAll('.token-chip').forEach(ch=>{
    ch.addEventListener('click',()=>insertToken(ch.dataset.target, ch.dataset.token));
});

document.getElementById('cat-execute').addEventListener('click', async ()=>{
    const payload = collectForm('#category-form');
    const sel = (catTree && catTree.jstree) ? catTree.jstree('get_selected') : [];
    payload['selected_categories[]'] = sel;
    payload['scope_categories'] = 'on';
    const res = await fetch('{{ route('admin.seo.bulk_meta.execute') }}', { method:'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: toParams(payload) });
    const data = await res.json();
    const catMsg = (data.updated_categories > 0) ? (data.updated_categories+' Kategori Güncellendi') : '0 Kategori Güncellendi';
    const dynMsg = (data.updated_dynamic_categories > 0) ? (data.updated_dynamic_categories+' Dinamik Kategori Güncellendi') : '0 Dinamik Kategori Güncellendi';
    const msg = catMsg + ' / ' + dynMsg;
    if (window.success) { window.success(msg); } else { alert(msg); }
    location.reload();
});

document.getElementById('prod-execute').addEventListener('click', async ()=>{
    const payload = collectForm('#product-form');
    const sel = (prodTree && prodTree.jstree) ? prodTree.jstree('get_selected') : [];
    // Product bulk meta uses only real categories.
    const catIds = (sel || [])
        .map(String)
        .filter((id)=> id.startsWith('c_') || /^\d+$/.test(id))
        .map((id)=> id.startsWith('c_') ? id.slice(2) : id)
        .filter((id)=> /^\d+$/.test(id));
    payload['categories[]'] = catIds;
    payload['scope_products'] = 'on';
    const res = await fetch('{{ route('admin.seo.bulk_meta.execute') }}', { method:'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: toParams(payload) });
    const data = await res.json();
    const msg = (data.updated_products > 0) ? (data.updated_products+' Ürün Güncellendi') : '0 Ürün Güncellendi';
    if (window.success) { window.success(msg); } else { alert(msg); }
    location.reload();
});

// Remove prefill behavior to keep forms clean after reload
</script>
@endpush
