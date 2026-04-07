<?php

namespace App\Mail;

use App\Mail\Transport\PetraNotifikasiTransport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * Class ExamplePetraMail
 *
 * Example Mailable implementation using Laravel Mailer.
 *
 * @use PetraNotifikasiTransport
 * pre-defined variables:
 * 'mail_subject = $email->getSubject(); -> subject of the email
 * 'mail_body'   = $email->getHtmlBody(); -> body of the email in HTML format (function content())
 *
 * This mail supports custom headers:
 * - template_kode   : nama template email yang ada di sistem notifikasi
 * - template_params : parameter yang dikirim ke template email (dalam format JSON)
 */
class ExamplePetraMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  string  $template_kode  nama template email yang ada di sistem notifikasi
     * @param  array  $template_params  parameter yang dikirim ke template email
     */
    public function __construct(
        // default values can be set here
        protected string $template_kode = 'DEFAULT_MAIL_TEMPLATE',
        protected array $template_params = []
    ) {
        //
    }

    /**
     * Get the message envelope definition.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Example Send Mail',
        );
    }

    /**
     * Get the message headers.
     *
     * Custom headers:
     * - template_kode
     * - template_params (JSON encoded)
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'template_kode' => $this->template_kode,
                'template_params' => json_encode($this->template_params),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.example',
            with: [
                'name' => $this->template_params['name'] ?? 'User',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
