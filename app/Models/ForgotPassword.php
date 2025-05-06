<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForgotPassword extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_profile_id',
        'code',
        'status',
        'expired'
    ];
}
