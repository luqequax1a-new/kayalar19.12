<?php

namespace Modules\Contact\Http\Controllers;

use Illuminate\Mail\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketMessage;
use Modules\Contact\Http\Requests\ContactRequest;

class ContactController
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('storefront::public.contact.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(ContactRequest $request)
    {
        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'order_id' => null,
            'subject' => $request->subject,
            'status' => 'waiting_admin',
            'last_message_at' => now(),
            'guest_email' => auth()->check() ? null : $request->email,
            'source' => 'contact',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'sender_type' => auth()->check() ? 'user' : 'guest',
            'body' => $request->message,
        ]);

        Mail::raw($request->message, function (Message $message) use ($request) {
            $message->subject($request->subject)
                ->replyTo($request->email)
                ->to(setting('store_email'));
        });

        return back()->with('success', trans('contact::messages.your_message_has_been_sent'));
    }
}
