<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $subjectText;

    public function __construct($otp, $subject = 'OTP Verification')
    {
        $this->otp = $otp;
        $this->subjectText = $subject;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
                    ->view('emails.otp')
                    ->with([
                        'otp' => $this->otp
                    ]);
    }
}
