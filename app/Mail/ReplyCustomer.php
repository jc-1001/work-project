<?php
namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReplyCustomer extends Mailable{
    
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contact_message) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "客服答覆通知",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reply_customer',
        );
    }
}