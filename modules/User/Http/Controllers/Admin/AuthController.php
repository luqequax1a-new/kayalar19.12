<?php

namespace Modules\User\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\User\Entities\User;
use Modules\User\Http\Controllers\BaseAuthController;

use Modules\Sms\Sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Modules\User\Mail\TwoFactorCodeMail;
use Modules\User\Http\Requests\LoginRequest;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;


class AuthController extends BaseAuthController
{
    /**
     * Show login form.
     *
     * @return Response
     */
    public function getLogin()
    {
        return view('user::admin.auth.login');
    }


    /**
     * Handle login request.
     *
     * @param LoginRequest $request
     * @return Response
     */
    public function postLogin(LoginRequest $request)
    {
        try {
            if (! setting('two_factor_auth_enabled')) {
                // 2FA disabled - login directly but redirect to admin dashboard
                $loggedIn = $this->auth->login([
                    'email' => $request->email,
                    'password' => $request->password,
                ], (bool) $request->get('remember_me', false));

                if (!$loggedIn) {
                    return back()->withInput()
                        ->withError(trans('user::messages.users.invalid_credentials'));
                }

                // Clear any intended URL and redirect to admin dashboard
                session()->forget(['url.intended', '_previous']);
                return redirect()->route('admin.dashboard.index');
            }

            $user = $this->auth->validate([
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if (! $user) {
                return back()->withInput()
                    ->withError(trans('user::messages.users.invalid_credentials'));
            }

            $this->sendTwoFactorCode($user);

            session([
                'admin_2fa_user_id' => $user->id,
                'admin_2fa_remember' => (bool) $request->get('remember_me', false),
            ]);

            return redirect()->route('admin.login.verify')
                ->withSuccess(trans('user::messages.users.2fa_code_sent'));
        } catch (NotActivatedException $e) {
            return back()->withInput()
                ->withError(trans('user::messages.users.account_not_activated'));
        } catch (ThrottlingException $e) {
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
        if (! session()->has('admin_2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('user::admin.auth.verify');
    }


    /**
     * Handle two factor verification request.
     *
     * @param Request $request
     * @return Response
     */
    public function postVerify(Request $request)
    {
        if (! session()->has('admin_2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        $user = User::findOrFail(session('admin_2fa_user_id'));

        if ($user->two_factor_code !== $request->code || $user->two_factor_expires_at->isPast()) {
            return back()->withError(trans('user::messages.users.invalid_2fa_code'));
        }

        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        $this->auth->loginById($user->id, session('admin_2fa_remember'));

        \Log::info('Admin 2FA Login - Before session forget', [
            'user_id' => $user->id,
            'intended' => session('url.intended'),
            'previous' => session('_previous'),
        ]);

        session()->forget(['admin_2fa_user_id', 'admin_2fa_remember', 'url.intended', '_previous']);

        \Log::info('Admin 2FA Login - Redirecting to dashboard');

        return redirect()->route('admin.dashboard.index');
    }


    /**
     * Resend verification code.
     *
     * @return Response
     */
    public function postResendCode()
    {
        if (! session()->has('admin_2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        $user = User::findOrFail(session('admin_2fa_user_id'));

        $this->sendTwoFactorCode($user);

        return back()->withSuccess(trans('user::messages.users.2fa_code_resent'));
    }


    public function postResendCodeSms()
    {
        if (! session()->has('admin_2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        $user = User::findOrFail(session('admin_2fa_user_id'));

        if (! $user->phone) {
            return back()->withError(trans('user::messages.users.no_phone_number'));
        }

        $this->sendTwoFactorCodeSms($user);

        return back()->withSuccess(trans('user::messages.users.2fa_code_resent_sms'));
    }


    /**
     * Send two factor verification code via SMS.
     *
     * @param User $user
     * @return void
     */
    protected function sendTwoFactorCodeSms(User $user)
    {
        $code = rand(100000, 999999);

        $user->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(5),
        ]);

        $message = trans('user::messages.users.2fa_sms_message', ['code' => $code]);

        Sms::send($user->phone, $message);
    }


    /**
     * Send two factor verification code.
     *
     * @param User $user
     * @return void
     */
    protected function sendTwoFactorCode(User $user)
    {
        $code = rand(100000, 999999);

        $user->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($user)->send(new TwoFactorCodeMail($user, $code));
    }



    /**
     * Show reset password form.

     *
     * @return Response
     */
    public function getReset()
    {
        return view('user::admin.auth.reset.begin');
    }


    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    protected function redirectTo()
    {
        return route('admin.dashboard.index');
    }


    /**
     * The login URL.
     *
     * @return string
     */
    protected function loginUrl()
    {
        return route('admin.login');
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
        return route('admin.reset.complete', [$user->email, $code]);
    }


    /**
     * Password reset complete view.
     *
     * @return string
     */
    protected function resetCompleteView()
    {
        return view('user::admin.auth.reset.complete');
    }
}
