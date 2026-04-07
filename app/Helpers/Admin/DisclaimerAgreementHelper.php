<?php

namespace App\Helpers\Admin;

use Exception;
use Illuminate\Support\Facades\Http;

class DisclaimerAgreementHelper
{
    public static function check()
    {
        try {
            $user_id = auth()->id();
            $url = config('url.service.disclaimer') . '/api/v1/agreement/check/' . $user_id . '/' . config('app.id');
            $response = Http::get($url);
            if ($response->successful()) {
                $data = $response->json();

                return $data['data']['status'];
            }

            return false;
        } catch (Exception $e) {
            // Handle exceptions
            throw_error('5000', ['location' => 'DisclaimerAgreementHelper@check', 'request' => null, 'detail' => 'Gagal konek API Disclaimer'], $e);

            return false;
        }
    }

    public static function base64custom_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64custom_decode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder); // restore padding
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function goToDisclaimerAgreeement($nextUrl)
    {
        $user_id = auth()->id();
        $param = [
            'user' => self::base64custom_encode($user_id),
            'app' => self::base64custom_encode(config('app.id')),
            'url' => self::base64custom_encode($nextUrl),
        ];

        // Build the URL with query parameters
        return $url = config('url.service.disclaimer') . '/agreement/' . $param['user'] . '/' . $param['app'] . '/' . $param['url'];
    }
}
