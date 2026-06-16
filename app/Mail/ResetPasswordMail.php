<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public string $resetUrl
    ) {}

    public function build(): static
    {
        return $this->subject('重設您的密碼')
                    ->markdown('emails.reset-password');
    }
}