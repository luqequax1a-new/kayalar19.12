@extends('user::admin.auth.layout')

@section('title', trans('user::auth.two_factor_verification'))

@section('content')
    <div class="bg-shape-layer"></div>

    <div class="bg-yellow-shape">
        <svg xmlns="http://www.w3.org/2000/svg" width="120" height="125" viewBox="0 0 120 125" fill="none">
            <path
                d="M125.143 158.853C47.8652 155.266 13.5195 136.007 6.00644 126.826C-20.7831 33.0517 48.5091 3.20269 86.504 0C-0.433365 64.0537 221.74 163.337 125.143 158.853Z"
                fill="#FFAD00" />
        </svg>
    </div>

    <div class="bg-green-shape">
        <svg xmlns="http://www.w3.org/2000/svg" width="148" height="71" viewBox="0 0 148 71" fill="none">
            <g filter="url(#filter0_d_2414_189)">
                <path
                    d="M4.74654 103C0.139535 55.1211 14.6977 -30.3244 109.786 10.9251C204.875 52.1747 79.3801 89.4957 4.74654 103Z"
                    fill="#00BC65" />
            </g>
            <defs>
                <filter id="filter0_d_2414_189" x="0" y="0" width="148" height="111"
                    filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                    <feColorMatrix in="SourceAlpha" type="matrix"
                        values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                    <feOffset dy="4" />
                    <feGaussianBlur stdDeviation="2" />
                    <feComposite in2="hardAlpha" operator="out" />
                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_2414_189" />
                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_2414_189" result="shape" />
                </filter>
            </defs>
        </svg>
    </div>

    <div class="auth-wrapper" x-data="otpHandler()" x-init="startTimer()">
        <a href="{{ route('admin.login') }}" class="back-to" title="{{ trans('user::auth.back_to_login') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M6.37992 3.95337L2.33325 8.00004L6.37992 12.0467" stroke="#0E1E3E" stroke-width="1.5"
                    stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M13.6668 8H2.44678" stroke="#0E1E3E" stroke-width="1.5" stroke-miterlimit="10"
                    stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>

        <div class="auth-left">
        </div>

        <div class="auth-right">
            <div class="bg-grid-shape"></div>
            <div class="bg-shape-layer"></div>

            <div class="bg-yellow-shape">
                <svg xmlns="http://www.w3.org/2000/svg" width="120" height="125" viewBox="0 0 120 125" fill="none">
                    <path
                        d="M125.143 158.853C47.8652 155.266 13.5195 136.007 6.00644 126.826C-20.7831 33.0517 48.5091 3.20269 86.504 0C-0.433365 64.0537 221.74 163.337 125.143 158.853Z"
                        fill="#FFAD00" />
                </svg>
            </div>

            <div class="bg-green-shape">
                <svg xmlns="http://www.w3.org/2000/svg" width="148" height="71" viewBox="0 0 148 71" fill="none">
                    <g filter="url(#filter0_d_2414_189)">
                        <path
                            d="M4.74654 103C0.139535 55.1211 14.6977 -30.3244 109.786 10.9251C204.875 52.1747 79.3801 89.4957 4.74654 103Z"
                            fill="#00BC65" />
                    </g>
                    <defs>
                        <filter id="filter0_d_2414_189" x="0" y="0" width="148" height="111"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="4" />
                            <feGaussianBlur stdDeviation="2" />
                            <feComposite in2="hardAlpha" operator="out" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_2414_189" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_2414_189" result="shape" />
                        </filter>
                    </defs>
                </svg>
            </div> 

            <div class="auth-form" style="background: transparent; box-shadow: none; border-radius: 0;">
                <style>
                    .tfa-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
                    .tfa-header-content h1 { font-size: 19px; font-weight: 800; color: #1e3a5f; margin: 0; }
                    .tfa-header-content p { font-size: 14px; color: #64748b; margin-top: 5px; line-height: 1.4; }
                    .tfa-header-icon { color: #64748b; }
                    
                    .tfa-label-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
                    .tfa-label { font-size: 14px; font-weight: 600; color: #475569; }
                    .tfa-timer { display: flex; align-items: center; gap: 6px; font-size: 16px; font-weight: 700; color: #fb923c; }

                    .otp-boxes { display: flex; gap: 10px; margin-bottom: 25px; justify-content: space-between; }
                    .otp-boxes input { width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: 700; border: 1.5px solid #cbd5e1; border-radius: 12px; color: #1e293b; background: #fff; transition: border-color 0.2s; }
                    .otp-boxes input:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); outline: none; }
                    .otp-boxes input::placeholder { color: #cbd5e1; font-size: 18px; opacity: 1; font-weight: 500; }

                    .tfa-notice { display: flex; gap: 10px; margin-bottom: 30px; font-size: 14px; color: #64748b; align-items: flex-start; line-height: 1.5; }
                    .tfa-notice .info-circle { flex-shrink: 0; width: 18px; height: 18px; border: 1.2px solid #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: serif; font-style: italic; font-weight: bold; font-size: 12px; color: #94a3b8; margin-top: 1px; }

                    .btn-verify-tfa { width: 100%; height: 55px; background: #a4b0be; color: #fff; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; transition: all 0.2s; }
                    .btn-verify-tfa:hover { background: #747d8c; }
                    .btn-verify-tfa:disabled { opacity: 0.8; cursor: not-allowed; }

                    .tfa-footer { margin-top: 30px; text-align: center; border-top: 1.5px solid #f1f5f9; padding-top: 25px; }
                    .tfa-footer-text { font-size: 14px; color: #64748b; margin-bottom: 15px; }
                    .btn-sms-tfa { width: 100%; height: 55px; background: #fff; color: #6366f1; border: 1.5px solid #6366f1; border-radius: 12px; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; transition: all 0.2s; }
                    .btn-sms-tfa:hover { background: #f5f3ff; }
                    
                    .sms-icon-wrapper { width: 22px; height: 22px; border: 1.5px solid #6366f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

                    .resend-link-tfa { background: none; border: none; color: #94a3b8; font-size: 12px; text-decoration: underline; text-underline-offset: 4px; text-decoration-style: dotted; cursor: pointer; margin-top: 20px; }
                    
                    .alert.alert-success { background-color: #f0fdf4 !important; color: #16a34a !important; border: none !important; border-radius: 12px !important; display: flex !important; align-items: center !important; gap: 10px !important; padding: 12px 15px !important; margin-bottom: 25px !important; font-size: 13px !important; }
                    .alert.alert-success .close { color: #16a34a !important; opacity: 0.5 !important; font-size: 18px !important; margin-left: auto !important; position: relative !important; top: 0 !important; right: 0 !important; }
                </style>

                <div class="tfa-header">
                    <div class="tfa-header-content">
                        <h1>{{ Modules\User\Entities\User::find(session('admin_2fa_user_id'))->email ?? 'admin@example.com' }}</h1>
                        <p>adresinize gönderdiğimiz<br>doğrulama kodunu giriniz.</p>
                    </div>
                    <div class="tfa-header-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="5" width="18" height="14" rx="3" stroke="#94A3B8" stroke-width="1.5"/>
                            <path d="M7 9L12 12.5L17 9" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>

                @include('user::admin.partials.notification')

                <form id="verify-form" method="POST" action="{{ route('admin.login.verify.post') }}" @submit.prevent="submitForm">
                    {{ csrf_field() }}
                    <input type="hidden" name="code" x-model="fullCode">

                    <div class="tfa-label-row">
                        <span class="tfa-label">Doğrulama Kodu</span>
                        <div class="tfa-timer">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" stroke="#FB923C" stroke-width="1.8"/>
                                <path d="M12 7V12L15 14" stroke="#FB923C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span x-text="timerText">05:00</span>
                        </div>
                    </div>

                    <div class="otp-boxes">
                        <template x-for="i in 6" :key="i">
                            <input 
                                type="text" 
                                maxlength="1" 
                                x-on:input="handleInput($event, i)" 
                                x-on:keydown="handleKeydown($event, i)"
                                x-on:paste="handlePaste($event)"
                                x-on:focus="$event.target.placeholder = ''"
                                x-on:blur="$event.target.placeholder = '-'"
                                placeholder="-"
                                inputmode="numeric"
                            >
                        </template>
                    </div>

                    <div class="tfa-notice">
                        <div class="info-circle">i</div>
                        <span>Gelen kutunuzu veya spam (önemsiz) klasörünüzü kontrol etmeyi unutmayınız.</span>
                    </div>

                    <button 
                        type="submit" 
                        class="btn-verify-tfa shadow-sm"
                        :disabled="fullCode.length !== 6 || submitting"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="5" y="11" width="14" height="10" rx="2" stroke="white" stroke-width="1.5"/>
                            <path d="M8 11V7C8 4.79 9.79 3 12 3C14.21 3 16 4.79 16 7V11" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="12" cy="16" r="1" fill="white"/>
                        </svg>
                        Doğrula
                    </button>
                </form>

                <div class="tfa-footer">
                    <div class="tfa-footer-text">E-posta kodunuz ulaşmadı mı?</div>

                    <form method="POST" action="{{ route('admin.login.verify.resend.sms') }}">
                        {{ csrf_field() }}
                        <button type="submit" class="btn-sms-tfa">
                            <div class="sms-icon-wrapper">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 18V12M12 12V6M12 12H18M12 12H6" stroke="#6366F1" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            SMS ile doğrulama yap
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.login.verify.resend') }}">
                        {{ csrf_field() }}
                        <button type="submit" class="resend-link-tfa">
                            Kodu Tekrar E-posta ile Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function otpHandler() {
            return {
                fullCode: '',
                timer: 300,
                timerText: '05:00',
                submitting: false,
                
                handleInput(e, index) {
                    const inputs = Array.from(document.querySelectorAll('.otp-boxes input'));
                    const val = e.target.value;
                    if (val && !/^\d$/.test(val)) { e.target.value = ''; return; }
                    this.updateFullCode();
                    if (val && index < 6) { inputs[index].focus(); }
                },
                
                handleKeydown(e, index) {
                    const inputs = Array.from(document.querySelectorAll('.otp-boxes input'));
                    if (e.key === 'Backspace' && !e.target.value && index > 1) { inputs[index-2].focus(); }
                },

                handlePaste(e) {
                    e.preventDefault();
                    const text = e.clipboardData.getData('text').slice(0, 6).replace(/\D/g, '');
                    const inputs = Array.from(document.querySelectorAll('.otp-boxes input'));
                    text.split('').forEach((char, i) => { if (inputs[i]) inputs[i].value = char; });
                    this.updateFullCode();
                    if (text.length > 0) { inputs[Math.min(text.length, 5)].focus(); }
                },
                
                updateFullCode() {
                    const inputs = Array.from(document.querySelectorAll('.otp-boxes input'));
                    this.fullCode = inputs.map(i => i.value).join('');
                },

                submitForm() {
                    if (this.fullCode.length === 6) {
                        this.submitting = true;
                        document.getElementById('verify-form').submit();
                    }
                },
                
                startTimer() {
                    const interval = setInterval(() => {
                        if (this.timer <= 0) { clearInterval(interval); return; }
                        this.timer--;
                        const mins = Math.floor(this.timer / 60);
                        const secs = this.timer % 60;
                        this.timerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    }, 1000);
                }
            }
        }
    </script>
@endsection
