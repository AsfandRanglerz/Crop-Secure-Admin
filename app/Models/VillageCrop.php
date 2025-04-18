<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VillageCrop extends Model
{
    use HasFactory;

    protected $guarded = [];

        public function crop()
    {
        return $this->belongsTo(EnsuredCropName::class, 'crop_name_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
