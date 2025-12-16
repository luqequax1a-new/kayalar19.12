<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox('storefront_faq_enabled', trans('storefront::attributes.section_status'), 'FAQ bölümünü etkinleştir', $errors, $settings) }}
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="panel-wrap">
            <div class="row">
                <div class="col-md-8">
                    {{ Form::text('storefront_faq_title', 'Başlık', $errors, $settings) }}

                    <p class="text-muted m-b-15" style="font-size: 12px;">
                        Ziyaretçilerin en çok merak ettiği soruları buraya ekleyin. İstediğiniz kadar soru / cevap ekleyebilirsiniz.
                    </p>

                    @php
                        $faqItems = $settings['storefront_faq_items'] ?? [];

                        if (! is_array($faqItems) || empty($faqItems)) {
                            $faqItems = [
                                ['question' => 'Kargo ne kadar sürede teslim edilir?', 'answer' => 'Hafta içi saat 15:00’e kadar verilen siparişler aynı gün kargoya verilir. Ortalama teslimat süresi 1-3 iş günüdür.'],
                                ['question' => 'Kargo ücreti ne kadar?', 'answer' => 'Sepet toplamınıza ve kampanyalara göre değişebilir. Uygun koşullarda ücretsiz kargo seçeneği sunulmaktadır.'],
                                ['question' => 'İadeyi nasıl yapabilirim?', 'answer' => 'Ürünü teslim aldıktan sonra 14 gün içinde, faturanızla birlikte anlaşmalı kargo firmamız üzerinden ücretsiz olarak iade edebilirsiniz.'],
                                ['question' => 'Ödeme seçenekleriniz nelerdir?', 'answer' => 'Kredi kartı, banka kartı, EFT/havale ve kapıda ödeme (uygun bölgelerde) seçenekleri sunuyoruz.'],
                                ['question' => 'Siparişimi nasıl takip ederim?', 'answer' => 'Hesabım → Siparişlerim menüsünden tüm siparişlerinizi ve kargo takip numaralarınızı görüntüleyebilirsiniz.'],
                            ];
                        }
                    @endphp

                    <div id="faq-items" class="faq-items panel panel-default" style="border-radius: 6px;">
                        <div class="panel-heading" style="border-bottom: 1px solid #eee; padding: 10px 15px;">
                            <strong>Soru &amp; Cevap Listesi</strong>
                        </div>

                        @foreach ($faqItems as $index => $item)
                            <div class="panel panel-default m-b-0" data-faq-index="{{ $index }}" style="border: 0; border-top: 1px solid #eee; border-radius: 0;">
                                <div class="panel-heading clearfix" style="background: #fafafa;">
                                    <div class="pull-left" style="width: 80%; padding-right: 10px;">
                                        <input
                                            type="text"
                                            name="storefront_faq_items[{{ $index }}][question]"
                                            class="form-control input-sm faq-question-input"
                                            placeholder="Soru"
                                            value="{{ $item['question'] ?? '' }}"
                                        >
                                    </div>

                                    <div class="pull-right text-right" style="width: 20%;">
                                        <button type="button" class="btn btn-xs btn-danger faq-remove"><i class="fa fa-times"></i></button>
                                    </div>
                                </div>

                                <div class="panel-body">
                                    <textarea
                                        name="storefront_faq_items[{{ $index }}][answer]"
                                        class="form-control input-sm faq-answer-textarea"
                                        rows="3"
                                        placeholder="Cevap"
                                    >{{ $item['answer'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-default m-t-15" id="add-faq-item">
                        Soru Ekle
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const container = document.getElementById('faq-items');
            const addButton = document.getElementById('add-faq-item');

            if (!container || !addButton) {
                return;
            }

            function getItems() {
                const rows = container.querySelectorAll('[data-faq-index]');
                const items = [];

                rows.forEach(function (row) {
                    const question = row.querySelector('.faq-question-input').value.trim();
                    const answer = row.querySelector('.faq-answer-textarea').value.trim();

                    if (question !== '' || answer !== '') {
                        items.push({ question: question, answer: answer });
                    }
                });

                return items;
            }

            function syncHiddenInput() {
                const items = getItems();

                // indexleri temizle ve yeniden sırala
                container.querySelectorAll('[data-faq-index]').forEach(function (row, newIndex) {
                    row.setAttribute('data-faq-index', newIndex);

                    const questionInput = row.querySelector('.faq-question-input');
                    const answerTextarea = row.querySelector('.faq-answer-textarea');

                    if (questionInput) {
                        questionInput.setAttribute('name', 'storefront_faq_items[' + newIndex + '][question]');
                    }

                    if (answerTextarea) {
                        answerTextarea.setAttribute('name', 'storefront_faq_items[' + newIndex + '][answer]');
                    }
                });
            }

            function createRow(item, index) {
                const row = document.createElement('div');
                row.className = 'panel panel-default m-b-10';
                row.setAttribute('data-faq-index', index);

                row.innerHTML = `
                    <div class="panel-heading clearfix">
                        <div class="pull-left" style="width: 80%;">
                            <input
                                type="text"
                                name="storefront_faq_items[${index}][question]"
                                class="form-control input-sm faq-question-input"
                                placeholder="Soru"
                                value="${item.question ? item.question.replace(/"/g, '&quot;') : ''}"
                            >
                        </div>

                        <div class="pull-right text-right" style="width: 20%;">
                            <button type="button" class="btn btn-xs btn-danger faq-remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>

                    <div class="panel-body">
                        <textarea
                            name="storefront_faq_items[${index}][answer]"
                            class="form-control input-sm faq-answer-textarea"
                            rows="3"
                            placeholder="Cevap"
                        >${item.answer ? item.answer : ''}</textarea>
                    </div>
                `;

                row.addEventListener('input', syncHiddenInput);

                row.querySelector('.faq-remove').addEventListener('click', function () {
                    row.parentNode.removeChild(row);
                    syncHiddenInput();
                });

                return row;
            }

            function render() {
                const rows = container.querySelectorAll('[data-faq-index]');

                // Mevcut satırlar zaten Blade ile render edildi, sadece event'leri bağla
                if (rows.length) {
                    rows.forEach(function (row, index) {
                        row.setAttribute('data-faq-index', index);

                        const questionInput = row.querySelector('.faq-question-input');
                        const answerTextarea = row.querySelector('.faq-answer-textarea');
                        const removeBtn = row.querySelector('.faq-remove');

                        if (questionInput) {
                            questionInput.addEventListener('input', syncHiddenInput);
                        }

                        if (answerTextarea) {
                            answerTextarea.addEventListener('input', syncHiddenInput);
                        }

                        if (removeBtn) {
                            removeBtn.addEventListener('click', function () {
                                row.parentNode.removeChild(row);
                                syncHiddenInput();
                            });
                        }
                    });

                    return;
                }

                // Hiç satır yoksa boş bir tane ekle
                const emptyItem = { question: '', answer: '' };
                container.appendChild(createRow(emptyItem, 0));
            }

            addButton.addEventListener('click', function () {
                const items = getItems();
                items.push({ question: '', answer: '' });

                const newIndex = container.querySelectorAll('[data-faq-index]').length;
                container.appendChild(createRow(items[items.length - 1], newIndex));
                syncHiddenInput();
            });

            render();
        })();
    </script>
@endpush
