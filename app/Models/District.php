<?php

namespace App\Models;

use App\Models\Tehsil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class District extends Model
{
    use HasFactory;
    protected $guarded=[];
    
    public function tehsils()
    {
        return $this->hasMany(Tehsil::class);
    }
}
