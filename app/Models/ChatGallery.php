<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatGallery extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable =[
        'use_id',
        'channel_id',
        'tag_id',
        'uuid',
        'topic',
        'name',
        'view',
        'official',
        'private',
        'live_schedule'
    ];

    public function category(){
        return $this->hasOne('App\Models\Channel','id','channel_id');
    }

    public function chatrooms(){
        return $this->hasMany('App\Models\ChatRoom');
    }
    
}
