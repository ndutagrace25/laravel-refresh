<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreatorVerification extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'known_as',
        'channel_id',
        'proof_of_work',
        'video',
        'photo_id',
        'tier',
        'status'
    ];

  

}
