<?php

namespace App\Models;

use App\Models\Tehsil;
use App\Models\Village;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Uc extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function tehsil()
    {
        return $this->belongsTo(Tehsil::class);
    }

    public function villages()
    {
        return $this->hasMany(Village::class);
    }
}
