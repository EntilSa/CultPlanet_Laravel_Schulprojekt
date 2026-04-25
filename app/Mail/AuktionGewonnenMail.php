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

class AuktionGewonnenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Glückwunsch! Du hast eine Auktion gewonnen – CultPlanet',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.auktion-gewonnen',
        );
    }

    public function attachments(): array
    {
        // pdf generieren und als anhang hinzufügen
        $pdf = Pdf::loadView('pdf.rechnung', ['order' => $this->order]);

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                'Rechnung-Auktion-'.$this->order->id.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
