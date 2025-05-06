<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'birthdate',
        'country_code',
        'phone',
        'is_officilal',
        'user_profile_stage',
        'email',
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }
}
