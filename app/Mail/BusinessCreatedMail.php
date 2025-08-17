<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BusinessCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $business;
    public $password;
    public $adminPanelUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $business, $password)
    {
        $this->user = $user;
        $this->business = $business;
        $this->password = $password;
        $this->adminPanelUrl = 'https://gpsfieldmanager.digifrica.com';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Business Account Has Been Created'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.business_created',
            with: [
                'user' => $this->user,
                'business' => $this->business,
                'password' => $this->password,
                'adminUrl' => $this->adminPanelUrl,
            ]
        );
    }
    
     /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Business Account Has Been Created')
                    ->replyTo('info@gps.digifrica.com') // Add Reply-To email
                    ->view('emails.business_created')
                    ->with([
                        'user' => $this->user,
                        'business' => $this->business,
                        'password' => $this->password,
                        'adminUrl' => $this->adminPanelUrl,
                    ]);
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

