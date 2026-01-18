<?php

namespace Modules\Ticket\Entities;

use Modules\User\Entities\User;
use Modules\Order\Entities\Order;
use Modules\Support\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Temporarily disabled to test ticket creation
        /*
        static::created(function ($ticket) {
            // Run notifications in background to not block ticket creation
            try {
                \Log::info('[TICKET] New ticket created', ['id' => $ticket->id]);
                
                // Create admin notification (synchronous, fast)
                \FleetCart\Services\NotificationService::newTicket($ticket);
                
                \Log::info('[TICKET] Notification created');
            } catch (\Throwable $e) {
                \Log::error('[TICKET] Failed to create notification', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't block ticket creation
            }
        });
        */
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }
}
