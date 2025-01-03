<?php

namespace App\Mail;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TravelOrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TravelOrder $travelOrder) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Travel Order Status Updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.travel-order-updated',
        );
    }
}
