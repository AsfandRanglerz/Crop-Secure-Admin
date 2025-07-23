<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceSubTypeSatelliteNDVI extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function land()
{
    return $this->belongsTo(Land::class);
}

}
