<div class="box">
    <div class="box-header">
        <h5>FAQ (Sık Sorulan Sorular)</h5>
    </div>

    <div class="col-md-9">
        <div id="faq-items" class="faq-items">
            @php($oldFaqs = old('faqs', isset($blogPost) && is_array($blogPost->faqs) ? $blogPost->faqs : []))

            @forelse ($oldFaqs as $index => $item)
                <div class="faq-item-row" style="background: #fff; border-radius: 8px; padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb;" data-index="{{ $index }}">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <input
                                type="text"
                                name="faqs[{{ $index }}][question]"
                                class="form-control"
                                style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 12px; font-size: 14px;"
                                placeholder="Soru girin..."
                                value="{{ $item['question'] ?? '' }}"
                            >
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="btn btn-sm" style="background: #f3f4f6; border: 1px solid #e5e7eb; color: #6b7280; padding: 6px 8px; border-radius: 4px;" title="Yukarı taşı">
                                <i class="fa fa-arrow-up" style="font-size: 12px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm" style="background: #f3f4f6; border: 1px solid #e5e7eb; color: #6b7280; padding: 6px 8px; border-radius: 4px;" title="Aşağı taşı">
                                <i class="fa fa-arrow-down" style="font-size: 12px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 6px 8px; border-radius: 4px;" title="Sil">
                                <i class="fa fa-times" style="font-size: 12px;"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <textarea
                            name="faqs[{{ $index }}][answer]"
                            class="form-control"
                            rows="3"
                            style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 12px; font-size: 14px; resize: vertical;"
                            placeholder="Cevap girin..."
                        >{{ $item['answer'] ?? '' }}</textarea>
                    </div>
                </div>
            @empty
                <div class="faq-item-row" style="background: #fff; border-radius: 8px; padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb;" data-index="0">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <input
                                type="text"
                                name="faqs[0][question]"
                                class="form-control"
                                style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 12px; font-size: 14px;"
                                placeholder="Soru girin..."
                                value=""
                            >
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="btn btn-sm" style="background: #f3f4f6; border: 1px solid #e5e7eb; color: #6b7280; padding: 6px 8px; border-radius: 4px;" title="Yukarı taşı">
                                <i class="fa fa-arrow-up" style="font-size: 12px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm" style="background: #f3f4f6; border: 1px solid #e5e7eb; color: #6b7280; padding: 6px 8px; border-radius: 4px;" title="Aşağı taşı">
                                <i class="fa fa-arrow-down" style="font-size: 12px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 6px 8px; border-radius: 4px;" title="Sil">
                                <i class="fa fa-times" style="font-size: 12px;"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <textarea
                            name="faqs[0][answer]"
                            class="form-control"
                            rows="3"
                            style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 12px; font-size: 14px; resize: vertical;"
                            placeholder="Cevap girin..."
                        ></textarea>
                    </div>
                </div>
            @endforelse
        </div>

        <button type="button" class="btn" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500;" id="add-faq-item">
            + Yeni Soru Ekle
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let faqIndex = {{ count($oldFaqs) }};
    
    // Add new FAQ item
    document.getElementById('add-faq-item').addEventListener('click', function() {
        const faqItems = document.getElementById('faq-items');
        const newIndex = faqIndex++;
        
        const newFaqItem = document.createElement('div');
        newFaqItem.className = 'faq-item-row';
        newFaqItem.style.background = '#fff';
        newFaqItem.style.borderRadius = '8px';
        newFaqItem.style.padding = '16px';
        newFaqItem.style.marginBottom = '12px';
        newFaqItem.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        newFaqItem.style.border = '1px solid #e5e7eb';
        newFaqItem.setAttribute('data-index', newIndex);
        
        newFaqItem.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <div style="flex: 1;">
                    <input
                        type="text"
                        name="faqs[${newIndex}][question]"
                        class="form-control"
                        style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 12px; font-size: 14px;"
                        placeholder="Soru girin..."
                        value=""
                    >
                </div>
                <div style="display: flex; gap: 6px;">
                    <button type="button" class="btn btn-sm" style="background: #f3f4f6; border: 1px solid #e5e7eb; color: #6b7280; padding: 6px 8px; border-radius: 4px;" title="Yukarı taşı">
                        <i class="fa fa-arrow-up" style="font-size: 12px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm" style="background: #f3f4f6; border: 1px solid #e5e7eb; color: #6b7280; padding: 6px 8px; border-radius: 4px;" title="Aşağı taşı">
                        <i class="fa fa-arrow-down" style="font-size: 12px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 6px 8px; border-radius: 4px;" title="Sil">
                        <i class="fa fa-times" style="font-size: 12px;"></i>
                    </button>
                </div>
            </div>
            <div>
                <textarea
                    name="faqs[${newIndex}][answer]"
                    class="form-control"
                    rows="3"
                    style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 12px; font-size: 14px; resize: vertical;"
                    placeholder="Cevap girin..."
                ></textarea>
            </div>
        `;
        
        faqItems.appendChild(newFaqItem);
        attachEventListeners(newFaqItem);
    });
    
    // Remove FAQ item
    function attachEventListeners(container) {
        // Find the remove button by its title attribute
        const removeBtn = container.querySelector('button[title="Sil"]');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const faqItems = document.getElementById('faq-items');
                if (faqItems.children.length > 1) {
                    container.remove();
                }
            });
        }
        
        // Move up/down functionality
        const upBtn = container.querySelector('button[title="Yukarı taşı"]');
        if (upBtn) {
            upBtn.addEventListener('click', function() {
                const prev = container.previousElementSibling;
                if (prev) {
                    container.parentNode.insertBefore(container, prev);
                }
            });
        }
        
        const downBtn = container.querySelector('button[title="Aşağı taşı"]');
        if (downBtn) {
            downBtn.addEventListener('click', function() {
                const next = container.nextElementSibling;
                if (next) {
                    container.parentNode.insertBefore(next, container);
                }
            });
        }
    }
    
    // Attach listeners to existing items
    document.querySelectorAll('.faq-item-row').forEach(attachEventListeners);
});
</script>
