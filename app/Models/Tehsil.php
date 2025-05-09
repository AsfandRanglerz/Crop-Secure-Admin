<?php

namespace App\Models;

use App\Models\Uc;
use App\Models\District;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tehsil extends Model
{
    use HasFactory;
    protected $guarded=[];
    
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function ucs()
    {
        return $this->hasMany(Uc::class);
    }
}
