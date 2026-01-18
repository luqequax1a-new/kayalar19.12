<?php

namespace Modules\Ticket\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketMessage;
use Modules\Ticket\Entities\TicketAttachment;
use Modules\Ticket\Admin\TicketTable;

class TicketController
{
    public function index()
    {
        return view('ticket::admin.tickets.index');
    }

    public function show($id)
    {
        $ticket = Ticket::with(['user', 'order', 'messages.attachments'])->findOrFail($id);

        return view('ticket::admin.tickets.show', compact('ticket'));
    }

    public function edit($id)
    {
        return $this->show($id);
    }

    public function storeMessage($id, Request $request)
    {
        $data = $request->validate([
            'body' => ['required', 'string'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $ticket = Ticket::with(['user', 'order'])->findOrFail($id);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'sender_type' => 'admin',
            'body' => $data['body'],
        ]);

        foreach ($request->file('images', []) as $file) {
            $path = Storage::disk('public')->putFile('tickets', $file);
            TicketAttachment::create([
                'message_id' => $message->id,
                'path' => $path,
                'original_name' => substr($file->getClientOriginalName(), 0, 255),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $ticket->update([
            'status' => 'waiting_customer',
            'last_message_at' => now(),
        ]);

        // Send notification to customer
        try {
            $customerEmail = $ticket->user?->email ?? $ticket->guest_email;

            if ($customerEmail) {
                \Illuminate\Support\Facades\Mail::to($customerEmail)
                    ->send(new \Modules\Ticket\Mail\TicketReplyCustomerMail($ticket, $message));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return back();
    }

    public function close($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => 'closed']);
        return back();
    }

    public function table(Request $request)
    {
        $query = Ticket::query()
            ->with(['user', 'order'])
            ->orderByDesc('last_message_at');

        return new TicketTable($query);
    }
}
