<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * 通知設定（notification_recipients の email 型）向けの汎用通知メール。
 * 件名と本文（プレーンテキスト）を渡して送信する。
 */
class GenericNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public string $bodyText;

    public function __construct(string $subjectLine, string $bodyText)
    {
        $this->subjectLine = $subjectLine;
        $this->bodyText = $bodyText;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generic-notification',
            with: [
                'subjectLine' => $this->subjectLine,
                'bodyText' => $this->bodyText,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
