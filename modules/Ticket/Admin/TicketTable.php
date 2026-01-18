<?php

namespace Modules\Ticket\Admin;

use Modules\Admin\Ui\AdminTable;

class TicketTable extends AdminTable
{
    public function make()
    {
        return $this->newTable()
            ->editColumn('subject', function ($ticket) {
                $subject = e($ticket->subject);
                
                // Add order badge if exists
                if ($ticket->order_id && $ticket->order) {
                    $orderNumber = $ticket->order->displayOrderNumber();
                    $subject .= ' <span style="margin-left:6px;padding:3px 8px;background:#e0e7ff;color:#4338ca;border-radius:4px;font-size:11px;font-weight:600;">📦 #' . e($orderNumber) . '</span>';
                }
                
                // Add source badge if from contact
                if ($ticket->source === 'contact') {
                    $subject .= ' <span style="margin-left:4px;padding:3px 8px;background:#fef3c7;color:#92400e;border-radius:4px;font-size:11px;font-weight:600;">📧 İletişim</span>';
                }
                
                return $subject;
            })
            ->editColumn('user', function ($ticket) {
                return optional($ticket->user)->email ?: ($ticket->guest_email ?? null);
            })
            ->editColumn('status', function ($ticket) {
                $status = (string) $ticket->status;
                switch ($status) {
                    case 'closed':
                        return 'Kapalı';
                    case 'waiting_admin':
                        return 'Admin Bekleniyor';
                    case 'waiting_customer':
                        return 'Müşteri Bekleniyor';
                    case 'open':
                    default:
                        return 'Açık';
                }
            })
            ->editColumn('created', function ($ticket) {
                return view('admin::partials.table.date')->with('date', $ticket->created_at);
            })
            ->editColumn('updated', function ($ticket) {
                return view('admin::partials.table.date')->with('date', $ticket->updated_at);
            })
            ->addColumn('actions', function ($ticket) {
                $url = route('admin.tickets.show', $ticket->id);
                return '<a href="' . e($url) . '" class="btn btn-primary btn-sm">Görüntüle</a>';
            })
            ->rawColumns(['subject', 'actions']);
    }
}
