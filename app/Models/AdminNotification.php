<?php

namespace FleetCart\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $appends = [
        'time_ago',
    ];

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    // Helpers
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIconClassAttribute()
    {
        $icons = [
            'new_order' => 'fa-shopping-bag',
            'abandoned_cart' => 'fa-shopping-cart',
            'cart_recovered' => 'fa-check-circle',
            'new_customer' => 'fa-user-plus',
            'low_stock' => 'fa-exclamation-triangle',
            'product_review' => 'fa-star',
            'contact_message' => 'fa-envelope',
            'new_ticket' => 'fa-comment',
            'new_question' => 'fa-question-circle',
        ];

        return $icons[$this->type] ?? 'fa-bell';
    }

    public function getColorClassAttribute()
    {
        $colors = [
            'blue' => 'notification-blue',
            'green' => 'notification-green',
            'orange' => 'notification-orange',
            'red' => 'notification-red',
            'purple' => 'notification-purple',
        ];

        return $colors[$this->color] ?? 'notification-blue';
    }
}
