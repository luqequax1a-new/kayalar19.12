<?php

namespace Modules\User\Http\Controllers;

use Exception;
use Illuminate\Http\Response;
use Modules\Page\Entities\Page;
use Modules\User\Entities\User;
use Modules\User\LoginProvider;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends BaseAuthController
{
    /**
     * Show login form.
     *
     * @return Response
     */
    public function getLogin()
    {
        return view('storefront::public.auth.login', [
            'providers' => LoginProvider::enabled(),
        ]);
    }


    /**
     * Login a user.
     *
     * @param \Modules\User\Http\Requests\LoginRequest $request
     *
     * @return Response
     */
    public function postLogin(\Modules\User\Http\Requests\LoginRequest $request)
    {
        try {
            // First, validate credentials without logging in
            $user = $this->auth->validate([
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if (!$user) {
                return back()->withInput()
                    ->withError(trans('user::messages.users.invalid_credentials'));
            }

            // Healthy & Dynamic Check:
            // Get the ID of the customer role from settings.
            $customerRoleId = setting('customer_role');

            // If the user has ANY role that is NOT the customer role, they are an admin/staff.
            $isAdmin = \DB::table('user_roles')
                ->where('user_id', $user->id)
                ->where('role_id', '!=', $customerRoleId)
                ->exists();

            // Debug log to trace login attempt - purely for observation
            \Log::info('Login attempt DYNAMIC DEBUG', [
                'email' => $request->email,
                'is_admin' => $isAdmin,
                'customer_role_id' => $customerRoleId
            ]);

            if ($isAdmin && setting('two_factor_auth_enabled')) {
                // Admin with 2FA enabled - send code and redirect to verification
                $code = rand(100000, 999999);

                $user->update([
                    'two_factor_code' => $code,
                    'two_factor_expires_at' => now()->addMinutes(5),
                ]);

                \Illuminate\Support\Facades\Mail::to($user)->send(new \Modules\User\Mail\TwoFactorCodeMail($user, $code));

                session([
                    'site_2fa_user_id' => $user->id,
                    'site_2fa_remember' => (bool) $request->get('remember_me', false),
                    'site_2fa_is_admin' => true,
                    'site_2fa_method' => 'email',
                ]);

                return redirect()->route('login.verify')
                    ->withSuccess(trans('user::messages.users.2fa_code_sent'));
            } elseif ($isAdmin) {
                // Admin without 2FA - login and redirect to admin dashboard
                $this->auth->login([
                    'email' => $request->email,
                    'password' => $request->password,
                ], (bool) $request->get('remember_me', false));

                session()->forget(['url.intended', '_previous']);
                return redirect()->route('admin.dashboard.index');
            }

            // Regular customer login (no 2FA for customers)
            $loggedIn = $this->auth->login([
                'email' => $request->email,
                'password' => $request->password,
            ], (bool)$request->get('remember_me', false));

            if (!$loggedIn) {
                return back()->withInput()
                    ->withError(trans('user::messages.users.invalid_credentials'));
            }

            return redirect()->intended($this->redirectTo());
        } catch (\Cartalyst\Sentinel\Checkpoints\NotActivatedException $e) {
            return back()->withInput()
                ->withError(trans('user::messages.users.account_not_activated'));
        } catch (\Cartalyst\Sentinel\Checkpoints\ThrottlingException $e) {
            return back()->withInput()
                ->withError(trans('user::messages.users.account_is_blocked', ['delay' => $e->getDelay()]));
        }
    }


    /**
     * Show two factor verification form.
     *
     * @return Response
     */
    public function getVerify()
    {
        if (! session()->has('site_2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('storefront::public.auth.verify');
    }


    /**
     * Handle two factor verification request.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function postVerify(\Illuminate\Http\Request $request)
    {
        if (! session()->has('site_2fa_user_id')) {
            return redirect()->route('login');
        }

        $user = User::findOrFail(session('site_2fa_user_id'));

        if ($user->two_factor_code !== $request->code || $user->two_factor_expires_at->isPast()) {
            return back()->withError(trans('user::messages.users.invalid_2fa_code'));
        }

        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        $this->auth->loginById($user->id, session('site_2fa_remember'));

        $isAdmin = session('site_2fa_is_admin');

        session()->forget(['site_2fa_user_id', 'site_2fa_remember', 'site_2fa_is_admin', 'site_2fa_method', 'url.intended', '_previous']);

        if ($isAdmin) {
            return redirect()->route('admin.dashboard.index');
        }

        return redirect($this->redirectTo());
    }


    /**
     * Resend verification code.
     *
     * @return Response
     */
    public function postResendCode()
    {
        if (! session()->has('site_2fa_user_id')) {
            return redirect()->route('login');
        }

        $user = User::findOrFail(session('site_2fa_user_id'));

        $code = rand(100000, 999999);

        $user->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(5),
        ]);

        \Illuminate\Support\Facades\Mail::to($user)->send(new \Modules\User\Mail\TwoFactorCodeMail($user, $code));

        session(['site_2fa_method' => 'email']);

        return back()->withSuccess('Yeni doğrulama kodu e-posta adresinize gönderildi.');
    }


    /**
     * Resend verification code via SMS.
     *
     * @return Response
     */
    public function postResendCodeSms()
    {
        if (! session()->has('site_2fa_user_id')) {
            return redirect()->route('login');
        }

        $user = User::findOrFail(session('site_2fa_user_id'));

        if (! $user->phone) {
            return back()->withError(trans('user::messages.users.no_phone_number'));
        }

        $code = rand(100000, 999999);

        $user->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(5),
        ]);

        $message = trans('user::messages.users.2fa_sms_message', ['code' => $code]);

        \Modules\Sms\Sms::send($user->phone, $message);

        session(['site_2fa_method' => 'sms']);

        return back()->withSuccess('Yeni doğrulama kodu telefonunuza SMS olarak gönderildi.');
    }


    /**
     * Redirect the user to the given provider authentication page.
     *
     * @param string $provider
     *
     * @return Response
     */
    public function redirectToProvider($provider)
    {
        if (!LoginProvider::isEnable($provider)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }


    /**
     * Obtain the user information from the given provider.
     *
     * @param string $provider
     *
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        if (!LoginProvider::isEnable($provider)) {
            abort(404);
        }

        try {
            $user = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', $e->getMessage());
        }

        if (User::registered($user->getEmail())) {
            auth()->login(
                User::findByEmail($user->getEmail())
            );

            return redirect($this->redirectTo());
        }

        [$firstName, $lastName] = $this->extractName($user->getName());

        $registeredUser = $this->auth->registerAndActivate([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->getEmail(),
            'phone' => '',
            'password' => str_random(),
        ]);

        $this->assignCustomerRole($registeredUser);

        auth()->login($registeredUser);

        return redirect($this->redirectTo());
    }


    /**
     * Show registrations form.
     *
     * @return Response
     */
    public function getRegister()
    {
        return view('storefront::public.auth.register', [
            'privacyPageUrl' => $this->getPrivacyPageUrl(),
            'providers' => LoginProvider::enabled(),
        ]);
    }


    /**
     * Show reset password form.
     *
     * @return Response
     */
    public function getReset()
    {
        return view('storefront::public.auth.reset.begin');
    }


    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    protected function redirectTo()
    {
        return route('home');
    }


    /**
     * The login URL.
     *
     * @return string
     */
    protected function loginUrl()
    {
        return route('login');
    }


    /**
     * Reset complete form route.
     *
     * @param User $user
     * @param string $code
     *
     * @return string
     */
    protected function resetCompleteRoute($user, $code)
    {
        return route('reset.complete', [$user->email, $code]);
    }


    /**
     * Password reset complete view.
     *
     * @return string
     */
    protected function resetCompleteView()
    {
        return view('storefront::public.auth.reset.complete');
    }


    private function extractName($name)
    {
        return explode(' ', $name, 2);
    }


    /**
     * Get privacy page url.
     *
     * @return string
     */
    private function getPrivacyPageUrl()
    {
        return Cache::tags('settings')->rememberForever('privacy_page_url', function () {
            return Page::urlForPage(setting('storefront_privacy_page'));
        });
    }
}
