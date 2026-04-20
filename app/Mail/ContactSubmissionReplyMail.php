<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactSubmissionReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactSubmission $contactSubmission,
        public string $replyMessage,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: your LoomCraft contact request',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-submission-reply',
            with: [
                'subjectLine' => 'Re: your LoomCraft contact request',
                'contactSubmission' => $this->contactSubmission,
                'replyMessage' => $this->replyMessage,
            ],
        );
    }
}
