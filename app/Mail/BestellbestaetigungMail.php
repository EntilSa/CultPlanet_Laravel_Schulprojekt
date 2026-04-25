<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BestellbestaetigungMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Deine Bestellung #'.$this->order->id.' bei CultPlanet',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.bestellbestaetigung',
        );
    }

    public function attachments(): array
    {
        // pdf generieren und als anhang hinzufügen
        $pdf = Pdf::loadView('pdf.rechnung', ['order' => $this->order]);

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                'Rechnung-'.$this->order->id.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
