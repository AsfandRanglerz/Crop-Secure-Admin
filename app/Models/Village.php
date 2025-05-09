<?php

namespace App\Models;

use App\Models\Uc;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Village extends Model
{
    use HasFactory;
    protected $guarded=[];
    
    public function uc()
    {
        return $this->belongsTo(Uc::class);
    }

        public function crops()
    {
        return $this->hasMany(VillageCrop::class);
    }
    
}
