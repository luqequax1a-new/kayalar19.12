<?php

namespace Modules\Ticket\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketMessage;
use Modules\Ticket\Entities\TicketAttachment;

class TicketController
{
    public function index()
    {
        $tickets = Ticket::where('user_id', auth()->id())
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('ticket::public.account.messages.index', compact('tickets'));
    }

    public function create()
    {
        return view('ticket::public.account.messages.create');
    }

    public function show($id)
    {
        $ticket = Ticket::where('user_id', auth()->id())
            ->with(['messages.attachments', 'order'])
            ->findOrFail($id);

        return view('ticket::public.account.messages.show', compact('ticket'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'order_id' => ['nullable', 'integer'],
            'body' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'file', 'max:5120'], // 5MB max
        ]);

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'order_id' => $data['order_id'] ?? null,
            'subject' => $data['subject'],
            'category' => $data['category'] ?? null,
            'status' => 'waiting_admin',
            'last_message_at' => now(),
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'sender_type' => 'user',
            'body' => $data['body'],
        ]);

        // Upload attachments
        try {
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
        } catch (\Throwable $e) {
            \Log::error('[TICKET] File upload failed', ['error' => $e->getMessage()]);
        }

        // Create admin notification
        try {
            \FleetCart\Services\NotificationService::newTicket($ticket);
        } catch (\Throwable $e) {
            \Log::error('[TICKET] Notification failed', ['error' => $e->getMessage()]);
        }

        // Send email to admin
        try {
            $ticketWithMessages = $ticket->load('messages');
            \Illuminate\Support\Facades\Mail::to(setting('store_email'))
                ->send(new \Modules\Ticket\Mail\NewTicketAdminMail($ticketWithMessages));
        } catch (\Throwable $e) {
            \Log::error('[TICKET] Email failed', ['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('account.tickets.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Destek talebiniz başarıyla oluşturuldu. En kısa sürede size dönüş yapacağız.'
            ]);
    }

    public function storeMessage($id, Request $request)
    {
        $data = $request->validate([
            'body' => ['required', 'string'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $ticket = Ticket::where('user_id', auth()->id())->findOrFail($id);

        if ($ticket->status === 'closed') {
            return back()->with('error', __('Bu ticket kapatıldı. Yeni mesaj gönderemezsiniz.'));
        }

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'sender_type' => 'user',
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
            'status' => 'waiting_admin',
            'last_message_at' => now(),
        ]);

        return back();
    }
}
