<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class UserEmail extends Model
{
    protected $table = 'user_emails';

    protected $fillable = [
        'user_id',
        'recipient',
        'subject',
        'template',
        'locale',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
