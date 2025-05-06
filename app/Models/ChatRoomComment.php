<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChatRoomReaction;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoomComment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'parent_id',
        'comment',
        'audio_comment',
        'video_comment',
        'commentable_id',
        'commentable_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
   
    public function reply()
    {
        return $this->hasMany(ChatRoomComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->reply()->with('replies');
    }

    public function reactions()
    {
        return $this->hasMany(ChatRoomReaction::class, 'chat_room_comment_id');
    }
}
