<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $appDownloadLink;
    public $businessName;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $password, $businessName)
    {
        $this->user = $user;
        $this->password = $password;
        $this->appDownloadLink = 'https://drive.google.com/file/d/1q9Hb80uTeDlKTmOj7wiZpXhacWtqhDV7/view?usp=drive_link'; // Replace with your actual app download URL
        $this->businessName = $businessName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to ' . $this->businessName)
                    ->replyTo('info@gps.digifrica.com') // Add Reply-To email
                    ->view('emails.welcome_user')
                    ->with([
                        'user' => $this->user,
                        'password' => $this->password,
                        'appDownloadLink' => $this->appDownloadLink,
                        'businessName' => $this->businessName,
                    ]);
    }
}
