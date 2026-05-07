<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BestellstatusGeaendertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ihre Bestellung #' . $this->order->id . ' – Status: ' . ucfirst($this->order->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.bestellstatus-geaendert',
        );
    }

    // kein pdf-anhang nötig – nur eine kurze statusmeldung
    public function attachments(): array
    {
        return [];
    }
}
