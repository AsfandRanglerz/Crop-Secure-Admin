<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBankDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function farmer()
    {
        return $this->belongsTo(\App\Models\Farmer::class, 'user_id');
    }
}
