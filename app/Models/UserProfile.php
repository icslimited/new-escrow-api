<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    public $fillable = ['user_id', 'first_name', 'last_name', 'other_name', 'phone_number', 'profile_picture', 'residential_address', 'state', 'lga'];
}
