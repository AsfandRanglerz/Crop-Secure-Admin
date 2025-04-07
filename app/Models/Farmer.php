<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Farmer extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $guarded=[];

    protected $dates = ['dob'];
}
