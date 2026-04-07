<?php

namespace App\Helpers\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NotificationHelper
{
    public static function getToken()
    {
        if (Cache::has('notif_token')) {
            return Cache::get('notif_token');
        } else {
            $res = Http::post(config('url.service.api.notification') . '/api/token', ['email' => env('MAIL_USERNAME'), 'password' => env('MAIL_PASSWORD')]);
            if ($res['token']) {
                Cache::put('notif_token', $res['token']);

                return $res['token'];
            }
        }
    }

    public static function sendEmail($template, $to, $param, $cc = null, $bcc = null)
    {
        $response = Http::withToken(NotificationHelper::getToken())->post(config('url.service.api.notification') . '/api/sendmail', ['mailto' => $to, 'templatekode' => $template, 'param' => json_encode($param), 'cc' => $cc, 'bcc' => $bcc])->json();
        if ($response['status'] != '1') {
            Cache::forget('notif_token');
            $response = Http::withToken(NotificationHelper::getToken())->post(config('url.service.api.notification') . '/api/sendmail', ['mailto' => $to, 'templatekode' => $template, 'param' => json_encode($param), 'cc' => $cc, 'bcc' => $bcc])->json();
        }

        return $response;
    }

    public static function sendWa($template, $to, $param)
    {
        $response = Http::withToken(NotificationHelper::getToken())->post(config('url.service.api.notification') . '/api/sendwa', ['to' => $to, 'templatekode' => $template, 'param' => json_encode($param)])->json();
        if ($response['status'] != '1') {
            Cache::forget('notif_token');
            $response = Http::withToken(NotificationHelper::getToken())->post(config('url.service.api.notification') . '/api/sendwa', ['to' => $to, 'templatekode' => $template, 'param' => json_encode($param)])->json();
        }

        return $response;
    }
}
