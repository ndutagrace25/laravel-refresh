<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreatorVerificationFiles extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'creator_verification_id',
        'type',
        'path',
        'status'
    ];
}
