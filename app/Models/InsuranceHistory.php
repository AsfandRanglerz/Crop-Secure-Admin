<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function crop()
    {
        return $this->belongsTo(EnsuredCropName::class, 'crop_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function tehsil()
    {
        return $this->belongsTo(Tehsil::class, 'tehsil_id');
    }

    public function insuranceType()
    {
        return $this->belongsTo(InsuranceType::class, 'insurance_type_id');
    }

    public function ensuredCrop()
    {
        return $this->belongsTo(\App\Models\EnsuredCropName::class, 'crop_id'); // or the correct FK
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\Farmer::class, 'user_id');
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'user_id');
    }
}
