<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'question',
        'user_id',
        'live_schedule_gallery_id'
    ];
}
