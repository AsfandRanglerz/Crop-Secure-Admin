<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceSubType extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function tehsil()
    {
        return $this->belongsTo(Tehsil::class, 'tehsil_id', 'id');
    }

    public function crop()
    {
        return $this->belongsTo(EnsuredCropName::class, 'crop_name_id', 'id');
    }

    public function villages()
    {
        return $this->hasMany(Village::class, 'sub_type_id'); 
    }
}
