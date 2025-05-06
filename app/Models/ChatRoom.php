<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "chat_gallery_id",
        "audio",
        "name",
        "user_id",
    ];
    
    
    public function parent_comments()
    {
        return $this->hasMany(ChatRoomComment::class, 'commentable')->whereNull('parent_id');
    }

    public function comments()
    {
        return $this->morphMany(ChatRoomComment::class, 'commentable')->whereNull('parent_id');
    }
    public function members()
    {
        return $this->hasMany(ChatRoomEntry::class, 'chat_room_id', 'id');
    }

    public function allcomments(){
        return $this->morphMany(ChatRoomComment::class, 'commentable');

    }
}
