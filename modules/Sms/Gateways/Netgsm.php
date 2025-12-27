<?php

namespace Modules\Sms\Gateways;

use Exception;
use Illuminate\Support\Facades\Http;
use Modules\Sms\GatewayInterface;
use Modules\Sms\Exceptions\SmsException;

class Netgsm implements GatewayInterface
{
    public function send(string $to, string $message)
    {
        try {
            $response = Http::get('https://api.netgsm.com.tr/sms/send/get/', [
                'usercode' => setting('netgsm_username'),
                'password' => setting('netgsm_password'),
                'gsmno' => $this->formatPhoneNumber($to),
                'message' => $message,
                'msgheader' => setting('netgsm_header'),
                'dil' => 'tr',
            ]);

            if ($response->failed() || !str_contains($response->body(), '00')) {
                throw new Exception('Netgsm Error: ' . $response->body());
            }
        } catch (Exception $e) {
            throw new SmsException('Netgsm: ' . $e->getMessage());
        }
    }


    public function client()
    {
        return null;
    }


    private function formatPhoneNumber(string $number): string
    {
        return str_replace(['+', ' ', '(', ')', '-'], '', $number);
    }
}
