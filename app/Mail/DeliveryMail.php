<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $delivery;
    public $transaction_id;
    public $sub_total;
    public $quantity;
    public $discount;
    public $total;
    public $image;
    public $description;
    public $product_type;
    public $title;
    public $invoice_link;
    public $email_button_text;
    public $email_button_url;


    public function __construct($delivery, $transaction_id, $sub_total, $quantity, $discount, $total, $image, $description, $product_type, $title,$invoice_link, $email_button_text, $email_button_url)
    {
        $this->delivery          = $delivery;
        $this->transaction_id    = $transaction_id;
        $this->sub_total         = $sub_total;
        $this->quantity          = $quantity;
        $this->discount          = $discount;
        $this->total             = $total;
        $this->image             = $image;
        $this->description       = $description;
        $this->product_type      = $product_type;
        $this->title             = $title;
        $this->invoice_link      = $invoice_link;
        $this->email_button_text = $email_button_text;
        $this->email_button_url  = $email_button_url;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Product Delivery Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
