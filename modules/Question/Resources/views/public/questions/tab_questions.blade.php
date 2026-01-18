<div id="questions" class="tab-pane questions-tab-root" x-data="ProductQuestions({ productId: {{ $product->id }} })" @shown-questions.window="init()">
    <div class="questions-container-minimal">
        
        <!-- Questions List / Empty State -->
        <div class="q-list-wrapper" :class="{ 'is-loading': fetching }">
            
            <!-- Skeleton / Loading -->
            <template x-if="fetching && totalQuestions === 0">
                <div class="q-loading-state py-5 text-center">
                    <div class="spinner-modern"></div>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!fetching && totalQuestions === 0 && !showForm">
                <div class="q-empty-state-minimal py-5">
                    <div class="q-empty-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: #cbd5e1;">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <h4 x-text="trans('question::questions.storefront.no_questions')"></h4>
                    <p class="text-muted" x-text="trans('question::questions.storefront.be_the_first')"></p>
                    <button class="btn btn-minimal-dark mt-4" @click="showForm = true">
                         {{ trans('question::questions.storefront.ask_a_question') }}
                    </button>
                </div>
            </template>

            <!-- Questions Header (Simple) -->
            <div class="q-header-simple mb-5" x-show="totalQuestions > 0">
                <h3 class="m-0">
                    {{ trans('question::questions.storefront.questions', ['count' => '']) }}
                    <span class="q-count" x-text="`(${totalQuestions})`"></span>
                </h3>
            </div>

            <!-- Actual List -->
            <div class="q-items-stack" x-show="totalQuestions > 0">
                <template x-for="(question, index) in questions.data" :key="index">
                    <div class="q-entry-minimal">
                        <!-- Question Part -->
                        <div class="q-question-side">
                            <div class="q-meta-top">
                                <span class="q-author" x-text="question.customer_name"></span>
                                <span class="q-dot"></span>
                                <span class="q-time" x-text="formatDate(question.created_at)"></span>
                            </div>
                            <div class="q-body-text">
                                <p x-text="question.question"></p>
                            </div>
                        </div>

                        <!-- Response Part -->
                        <template x-if="question.answer">
                            <div class="q-answer-side">
                                <div class="q-admin-line">
                                    <span class="q-admin-label">{{ setting('store_name') }}</span>
                                    <span class="q-verified-icon"><i class="las la-check"></i></span>
                                </div>
                                <div class="q-answer-text">
                                    <p x-text="question.answer"></p>
                                </div>
                                <div class="q-answer-meta" x-text="trans('question::questions.storefront.answered_at', { date: formatDate(question.updated_at) })"></div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Pagination -->
            <template x-if="questions.last_page > 1">
                <div class="q-pagination-minimal mt-5" x-show="totalQuestions > 0">
                    @include('storefront::public.partials.pagination')
                </div>
            </template>
        </div>

        <!-- Question Form Section -->
        <div id="question-form" class="q-form-minimal mt-5 pt-5" x-show="totalQuestions > 0 || showForm" x-transition x-cloak>
            <div class="q-form-inner">
                <div class="q-form-header-minimal mb-5 text-center">
                    <h2>{{ trans('question::questions.storefront.ask_a_question') }}</h2>
                    <p>{{ trans('Ürün hakkında merak ettiklerinizi bize iletebilirsiniz.') }}</p>
                </div>

                <form @submit.prevent="submitQuestion" @input="errors.clear($event.target.name)">
                    @honeypot
                    <div class="q-fields-grid">
                        <template x-if="!isLoggedIn">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="minimal-input-wrap">
                                        <label>{{ trans('question::questions.form.customer_name') }}</label>
                                        <input type="text" name="customer_name" x-model="form.customer_name" required>
                                        <template x-if="errors.has('customer_name')">
                                            <span class="q-error-msg" x-text="errors.get('customer_name')"></span>
                                        </template>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="minimal-input-wrap">
                                        <label>{{ trans('question::questions.form.customer_email') }}</label>
                                        <input type="email" name="customer_email" x-model="form.customer_email" required>
                                        <template x-if="errors.has('customer_email')">
                                            <span class="q-error-msg" x-text="errors.get('customer_email')"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="minimal-input-wrap mb-5">
                            <label>{{ trans('question::questions.question') }}</label>
                            <textarea name="question" rows="4" x-model="form.question" :placeholder="trans('question::questions.storefront.write_your_question')" required></textarea>
                            <template x-if="errors.has('question')">
                                <span class="q-error-msg" x-text="errors.get('question')"></span>
                            </template>
                        </div>

                        <div class="q-submit-area">
                            <button type="submit" class="btn-minimal-black" :disabled="submitting">
                                <span x-show="!submitting">{{ trans('question::questions.storefront.submit') }}</span>
                                <span x-show="submitting"><div class="dots-loader"></div></span>
                            </button>
                            
                            <template x-if="totalQuestions === 0 && showForm">
                                <button type="button" class="btn-minimal-link mt-4" @click="showForm = false">
                                    {{ trans('Vazgeç') }}
                                </button>
                            </template>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    
    .questions-container-minimal {
        max-width: 900px;
        margin: 0 auto;
    }

    /* Header Simple */
    .q-header-simple h3 {
        font-size: 24px;
        font-weight: 800;
        color: #111;
        letter-spacing: -0.5px;
    }
    .q-count {
        color: #111;
        font-weight: 600;
        margin-left: 5px;
    }

    /* Stack Entries */
    .q-items-stack {
        display: flex;
        flex-direction: column;
        gap: 48px;
    }
    .q-entry-minimal {
        position: relative;
    }
    .q-entry-minimal:not(:last-child):after {
        content: '';
        position: absolute;
        bottom: -24px;
        left: 0;
        width: 100%;
        height: 1px;
        background: #f1f5f9;
    }

    /* Question Side */
    .q-meta-top {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .q-author {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }
    .q-dot {
        width: 4px;
        height: 4px;
        background: #111;
        border-radius: 50%;
    }
    .q-time {
        font-size: 13px;
        color: #111;
        font-weight: 500;
    }
    .q-body-text p {
        font-size: 15px;
        color: #111;
        line-height: 1.7;
        margin: 0;
    }

    /* Answer Side */
    .q-answer-side {
        margin-top: 24px;
        margin-left: 30px;
        padding: 24px;
        background: #f8fafc;
        border-radius: 12px;
        border-left: 3px solid #111;
    }
    .q-admin-line {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
    }
    .q-admin-label {
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #111;
    }
    .q-verified-icon {
        color: #10b981;
        font-size: 14px;
    }
    .q-answer-text p {
        font-size: 15px;
        color: #334155;
        line-height: 1.6;
        margin: 0;
    }
    .q-answer-meta {
        font-size: 12px;
        color: #111;
        margin-top: 12px;
        text-align: right;
        font-weight: 500;
    }

    /* Minimal Empty State */
    .q-empty-state-minimal {
        text-align: center;
        padding: 60px 20px;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        background: transparent;
    }
    .q-empty-icon {
        margin-bottom: 24px;
    }
    .q-empty-state-minimal h4 {
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
    }
    .btn-minimal-dark {
        background: #111;
        color: #fff !important;
        border-radius: 50px;
        padding: 12px 32px;
        font-weight: 700;
        font-size: 14px;
        border: none;
        transition: 0.2s;
    }
    .btn-minimal-dark:hover {
        background: #333;
        transform: translateY(-2px);
    }

    /* Minimal Form */
    .q-form-inner {
        max-width: 650px;
        margin: 0 auto;
    }
    .q-form-header-minimal h2 {
        font-size: 32px;
        font-weight: 900;
        letter-spacing: -1px;
        color: #111;
        margin-bottom: 10px;
    }
    .q-form-header-minimal p {
        color: #64748b;
        font-size: 15px;
    }

    .minimal-input-wrap {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .minimal-input-wrap label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #475569;
    }
    .minimal-input-wrap input, 
    .minimal-input-wrap textarea {
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px;
        font-size: 15px;
        transition: 0.2s;
        background: #fff;
    }
    .minimal-input-wrap input:focus, 
    .minimal-input-wrap textarea:focus {
        border-color: #111;
        outline: none;
        box-shadow: 0 0 0 4px rgba(0,0,0,0.03);
    }
    .q-error-msg {
        color: #ef4444;
        font-size: 12px;
        font-weight: 600;
    }

    .btn-minimal-black {
        background: #111;
        color: #fff !important;
        border: none;
        width: 100%;
        padding: 16px;
        border-radius: 14px;
        font-weight: 800;
        font-size: 16px;
        transition: 0.2s;
    }
    .btn-minimal-black:hover:not(:disabled) {
        background: #333;
    }
    .btn-minimal-black:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    .btn-minimal-link {
        border: none;
        background: none;
        font-weight: 700;
        color: #64748b;
        transition: 0.2s;
    }
    .btn-minimal-link:hover {
        color: #111;
    }

    /* Dots Loader */
    .dots-loader {
        width: 8px;
        height: 8px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 12px 0 #fff, -12px 0 #fff;
        animation: dots 1s infinite alternate;
        margin: 0 auto;
    }
    @keyframes dots {
        0% { opacity: 0.2; }
        100% { opacity: 1; }
    }
</style>
