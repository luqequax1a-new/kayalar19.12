<?php

namespace Modules\User\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TwoFactorCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var \Modules\User\Entities\User
     */
    public $user;

    /**
     * The verification code.
     *
     * @var string
     */
    public $code;

    /**
     * Create a new message instance.
     *
     * @param \Modules\User\Entities\User $user
     * @param string $code
     * @return void
     */
    public function __construct($user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(trans('user::mail.two_factor_code_subject'))
            ->view('storefront::public.emails.two_factor_code');
    }
}
