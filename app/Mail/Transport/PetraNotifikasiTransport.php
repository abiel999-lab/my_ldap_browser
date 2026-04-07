<?php

namespace App\Mail\Transport;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class PetraNotifikasiTransport extends AbstractTransport
{
    public function __construct(
        protected string $apiUrl,
        protected string $username,
        protected string $password,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $headers = $email->getHeaders();

        $templatekode = $headers->get('template_kode')?->getBodyAsString();

        $params = json_decode($headers->get('template_params')?->getBodyAsString(), true) ?? [];

        if (! $templatekode) {
            throw new RuntimeException('Petra mail requires templatekode');
        }

        $token = $this->getToken();
        $params['mail_subject'] = $email->getSubject();
        $params['mail_body'] = $email->getHtmlBody();

        $params = json_encode($params);
        foreach ($email->getTo() as $to) {
            try {
                $data = [
                    'mailto' => $to->getAddress(),
                    'templatekode' => $templatekode,
                    'param' => $params,
                    'istrial' => $this->istrialCheck() ? '1' : '0',
                ];
                /** @var Response $response */
                $response = Http::withToken($token)->post($this->apiUrl . '/api/sendmail', $data);

            } catch (Exception $e) {
                Log::error($e);
                throw new RuntimeException('Failed to send email');
            }
            if ($response->json()['status'] != 1) {
                Log::error(json_encode($response->json()));
                throw new RuntimeException('Failed to send email: ' . json_encode($response->json()) . $token . json_encode($data));
            } else {
                Log::info(json_encode($response->json()));
            }
        }
    }

    public function __toString(): string
    {
        return 'petra_notifikasi';
    }

    private function getToken()
    {
        // get token
        try {
            $token = Cache::remember('mail_api_token', 60, function () {
                /** @var Response $response */
                $response = Http::post($this->apiUrl . '/api/token', [
                    'email' => $this->username,
                    'password' => $this->password,
                ]);

                if ($response->status() !== 201) {
                    throw new Exception('Failed to get token');
                }

                return $response->json()['token'];
            });

            return $token;

        } catch (Exception $e) {
            Log::error($e);
            throw new RuntimeException($e);
        }
    }

    private function istrialCheck()
    {
        if (env('APP_ENV', 'local') != 'production') {
            return true;
        }
    }
}
