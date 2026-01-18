<?php

namespace Modules\Ticket\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Ticket\Entities\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewTicketAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        $replyTo = $this->ticket->user?->email ?: $this->ticket->guest_email;

        $mail = $this
            ->subject('Yeni Destek Talebi: ' . $this->ticket->subject)
            ->view('ticket::emails.new_ticket_admin');

        if ($replyTo) {
            $mail->replyTo($replyTo);
        }

        return $mail;
    }
}
