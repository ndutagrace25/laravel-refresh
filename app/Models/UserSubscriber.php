<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscriber_id',
        'subscription_id',
        'user_id',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
