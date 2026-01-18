<?php

namespace Modules\User\Listeners;

use Modules\User\Entities\User;
use Modules\User\Entities\UserEmail;
use Illuminate\Mail\Events\MessageSent;

class LogSentEmail
{
    /**
     * Handle the event.
     *
     * @param MessageSent $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        $message = $event->message;
        $recipients = $message->getTo();
        
        if (empty($recipients)) {
            return;
        }

        $recipientEmail = $recipients[0]->getAddress();
        $user = User::where('email', $recipientEmail)->first();
        
        // Skip if recipient is the store email AND they are not a registered user
        // This allows logging for admin-customers but skips generic admin notifications
        if ($recipientEmail === setting('store_email') && !$user) {
            return;
        }

        // Final check: only log if we have a user
        if (!$user) {
            return;
        }
        
        UserEmail::create([
            'user_id' => $user->id,
            'recipient' => $recipientEmail,
            'subject' => $message->getSubject(),
            'template' => $this->guessTemplate($event),
            'locale' => locale(),
        ]);
    }

    private function guessTemplate($event)
    {
        $subject = $event->message->getSubject();
        $dataKeys = array_keys($event->data);

        // Check by subject hints
        if (str_contains($subject, 'Sipariş') || in_array('order', $dataKeys)) {
            return 'order_conf';
        }

        if (str_contains($subject, 'Şifre') || in_array('resetUrl', $dataKeys)) {
            return 'reset_password';
        }

        if (str_contains($subject, 'Hoş geldiniz') || str_contains($subject, 'Welcome')) {
            return 'welcome_mail';
        }

        if (str_contains($subject, 'Kupon') || in_array('coupon', $dataKeys)) {
            return 'coupon_info';
        }

        if (in_array('cart', $dataKeys)) {
            return 'abandoned_cart';
        }

        // Fallback to class name if any module object is found
        foreach ($event->data as $value) {
            if (is_object($value)) {
                $class = get_class($value);
                if (str_contains($class, 'Modules\\')) {
                    $base = \Illuminate\Support\Str::snake(class_basename($value));
                    if (str_contains($base, 'mail')) {
                        return $base;
                    }
                }
            }
        }

        return 'system_mail';
    }
}
