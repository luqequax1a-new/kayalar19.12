@extends('storefront::public.auth.layout')

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

    @php 
        $user = Modules\User\Entities\User::find(session('site_2fa_user_id'));
        $currentMethod = session('site_2fa_method', 'email');
    @endphp

    <div class="auth-wrapper" x-data="otpHandler('{{ $currentMethod }}')" x-init="startTimer()">
        <a href="{{ route('login') }}" class="back-to" title="{{ trans('user::auth.back_to_login') }}">
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

            <div class="auth-form-overflow" style="display: flex; align-items: center; justify-content: center;">
                <div class="auth-form" style="background: transparent; box-shadow: none; border-radius: 0; width: 100%; max-width: 400px;">
                    <style>
                        .tfa-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; gap: 15px; }
                        .tfa-header-content { flex: 1; }
                        .tfa-header-content h1 { font-size: 19px; font-weight: 800; color: #1e3a5f; margin: 0; word-break: break-all; }
                        .tfa-header-content p { font-size: 14px; color: #64748b; margin-top: 5px; line-height: 1.4; }
                        .tfa-header-icon { color: #0068e1; width: 44px; height: 44px; background: #f0f7ff; border: 1px solid #e0efff; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
                        
                        .tfa-label-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
                        .tfa-label { font-size: 14px; font-weight: 600; color: #475569; }
                        .tfa-timer { display: flex; align-items: center; gap: 6px; font-size: 16px; font-weight: 700; color: #fb923c; }

                        .otp-boxes { display: flex; gap: 8px; margin-bottom: 25px; justify-content: space-between; }
                        .otp-boxes input { width: 100%; height: 60px; text-align: center; font-size: 24px; font-weight: 700; border: 1.5px solid #cbd5e1; border-radius: 12px; color: #1e293b; background: #fff; transition: all 0.2s; }
                        .otp-boxes input:focus { border-color: #0068e1; box-shadow: 0 0 0 4px rgba(0, 104, 225, 0.1); outline: none; }
                        .otp-boxes input::placeholder { color: #cbd5e1; font-size: 18px; opacity: 0.5; }

                        .tfa-notice { display: flex; gap: 10px; margin-bottom: 30px; font-size: 13px; color: #64748b; align-items: flex-start; line-height: 1.5; padding: 12px; background: #f8fafc; border-radius: 10px; }
                        .tfa-notice .info-circle { flex-shrink: 0; width: 18px; height: 18px; background: #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; color: #fff; margin-top: 1px; }

                        .btn-verify-tfa { width: 100%; height: 55px; background: #0068e1; color: #fff !important; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; transition: all 0.2s; }
                        .btn-verify-tfa:hover { background: #0056b3; }
                        .btn-verify-tfa:disabled { background: #cbd5e1; cursor: not-allowed; }

                        .tfa-footer { margin-top: 30px; text-align: center; border-top: 1.5px solid #f1f5f9; padding-top: 25px; }
                        .tfa-footer-text { font-size: 14px; color: #64748b; margin-bottom: 15px; font-weight: 500; }
                        
                        .btn-switch-tfa { width: 100%; height: 50px; background: #fff; color: #0068e1 !important; border: 1.5px solid #0068e1; border-radius: 12px; font-size: 15px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; transition: all 0.2s; margin-bottom: 20px; }
                        .btn-switch-tfa:hover { background: #f0f7ff; }
                        .btn-switch-tfa svg { flex-shrink: 0; }
                        
                        .resend-link-tfa { background: none; border: none; color: #94a3b8; font-size: 13px; font-weight: 600; text-decoration: underline; text-underline-offset: 4px; cursor: pointer; transition: color 0.2s; }
                        .resend-link-tfa:hover { color: #0068e1; }
                        
                        .alert { border-radius: 12px !important; display: flex !important; align-items: center !important; gap: 10px !important; padding: 12px 15px !important; margin-bottom: 25px !important; font-size: 13px !important; }
                        .alert-success { background-color: #f0fdf4 !important; color: #16a34a !important; border: 1px solid #dcfce7 !important; }
                        .alert-danger { background-color: #fef2f2 !important; color: #dc2626 !important; border: 1px solid #fee2e2 !important; }
                    </style>

                    <div class="tfa-header">
                        <div class="tfa-header-content">
                            @if ($currentMethod === 'email')
                                <h1>{{ $user->email ?? '' }}</h1>
                                <p>adresinize gönderilen<br>doğrulama kodunu giriniz.</p>
                            @else
                                <h1>{{ $user->phone ?? '' }}</h1>
                                <p>telefonunuza gönderilen<br>doğrulama kodunu giriniz.</p>
                            @endif
                        </div>
                        <div class="tfa-header-icon">
                            @if ($currentMethod === 'email')
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            @else
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            @endif
                        </div>
                    </div>

                    @include('storefront::public.auth.partials.notification')

                    <form id="verify-form" method="POST" action="{{ route('login.verify.post') }}" @submit.prevent="submitForm">
                        @csrf
                        <input type="hidden" name="code" x-model="fullCode">

                        <div class="tfa-label-row">
                            <span class="tfa-label">Doğrulama Kodu</span>
                            <div class="tfa-timer">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span x-text="timerText">10:00</span>
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
                            @if ($currentMethod === 'email')
                                <span>Gelen kutunuzu veya spam klasörünüzü kontrol etmeyi unutmayınız.</span>
                            @else
                                <span>Telefonunuzun mesaj kutusunu kontrol ediniz. Kodun ulaşması birkaç dakika sürebilir.</span>
                            @endif
                        </div>

                        <button 
                            type="submit" 
                            class="btn-verify-tfa"
                            :disabled="fullCode.length !== 6 || submitting"
                        >
                            <span>Doğrula</span>
                        </button>
                    </form>

                    <div class="tfa-footer">
                        <div class="tfa-footer-text">Sorun mu yaşıyorsunuz?</div>

                        {{-- Main Switch Button --}}
                        @if ($currentMethod === 'email')
                            @if ($user && $user->phone)
                                <form method="POST" action="{{ route('login.verify.resend.sms') }}">
                                    @csrf
                                    <button type="submit" class="btn-switch-tfa">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                        SMS ile Doğrula
                                    </button>
                                </form>
                            @endif
                        @else
                            <form method="POST" action="{{ route('login.verify.resend') }}">
                                @csrf
                                <button type="submit" class="btn-switch-tfa">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    E-posta ile Doğrula
                                </button>
                            </form>
                        @endif

                        {{-- Secondary Resend Link --}}
                        <form method="POST" action="{{ $currentMethod === 'email' ? route('login.verify.resend') : route('login.verify.resend.sms') }}">
                            @csrf
                            <button type="submit" class="resend-link-tfa">
                                @if ($currentMethod === 'email')
                                    Kodu Tekrar E-posta Gönder
                                @else
                                    Kodu Tekrar SMS Gönder
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function otpHandler(initialMethod) {
            return {
                fullCode: '',
                method: initialMethod,
                timer: 300,
                timerText: '05:00',
                submitting: false,
                
                handleInput(e, index) {
                    const inputs = Array.from(document.querySelectorAll('.otp-boxes input'));
                    const val = e.target.value.replace(/\D/g, '');
                    e.target.value = val;
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
