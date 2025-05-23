<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatGalleryFile extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "user_id",
        "uuid",
        "chat_gallery_id",
        "cover_photo",
        "greeting_audio",
        "greeting_vidoe"
    ];
}
