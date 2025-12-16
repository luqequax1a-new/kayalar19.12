@if (!empty($faq['items']))
    <section class="faq-section">
        <div class="container">
            <div class="faq-inner">
                <div class="faq-header">
                    <h2 class="faq-title">{{ $faq['title'] }}</h2>
                    <p class="faq-subtitle">En çok merak edilen soruları senin için bir araya getirdik.</p>
                </div>

                <div class="faq-content">
                    <div class="faq-intro">
                        <p>
                            Sipariş, kargo, iade ve ödeme yöntemleriyle ilgili aklına takılan soruların cevaplarını aşağıdaki listede bulabilirsin.
                        </p>
                    </div>

                    <div class="faq-accordion">
                    @foreach ($faq['items'] as $index => $item)
                        <details class="faq-item" {{ $index === 0 ? 'open' : '' }}>
                            <summary class="faq-question">
                                <span>{{ $item['question'] }}</span>

                                <span class="faq-toggle-icon" aria-hidden="true">
                                    <span class="line line-1"></span>
                                    <span class="line line-2"></span>
                                </span>
                            </summary>

                            <div class="faq-answer">
                                <p>{!! nl2br(e($item['answer'])) !!}</p>
                            </div>
                        </details>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
