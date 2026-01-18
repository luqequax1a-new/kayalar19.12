<?php

namespace Modules\Ticket\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketReplyCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public TicketMessage $ticketMessage;

    public function __construct(Ticket $ticket, TicketMessage $ticketMessage)
    {
        $this->ticket = $ticket;
        $this->ticketMessage = $ticketMessage;
    }

    public function build()
    {
        return $this
            ->subject('Destek Talebinize Yanıt Verildi: ' . $this->ticket->subject)
            ->view('ticket::emails.ticket_reply_customer');
    }
}
