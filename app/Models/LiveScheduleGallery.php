<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveScheduleGallery extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable =[
        'entry_fee',
        'user_id',
        'product_id',
        'uuid',
        'is_premium',
        'start_time',
        'cover_photo',
        'end_time',
        'channel_id',
        'tag_id',
        'title',
        'description',
        'date',
        'timezone',
        'pre-live',
        'url',
        'stream_key'
    ];
}
