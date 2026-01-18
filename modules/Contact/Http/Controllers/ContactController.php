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
        // Find order_id from order_number if provided
        $orderId = null;
        if ($request->filled('order_number')) {
            $orderNumber = trim(str_replace('#', '', $request->order_number));
            
            // Try to find order by ID or by order_number
            $order = \Modules\Order\Entities\Order::query()
                ->where('id', $orderNumber)
                ->orWhere('order_number', $orderNumber)
                ->first();
            
            if ($order) {
                $orderId = $order->id;
            }
        }

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'order_id' => $orderId,
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

        // Send email to admin
        try {
            $ticket->load(['messages', 'order']);
            Mail::to(setting('store_email'))
                ->send(new \Modules\Ticket\Mail\NewTicketAdminMail($ticket));
        } catch (\Throwable $e) {
            \Log::error('[CONTACT] Email failed', ['error' => $e->getMessage()]);
        }

        // Create admin notification
        try {
            \FleetCart\Services\NotificationService::newTicket($ticket);
        } catch (\Throwable $e) {
            \Log::error('[CONTACT] Notification failed', ['error' => $e->getMessage()]);
        }

        return back()->with('success', trans('contact::messages.your_message_has_been_sent'));
    }
}
