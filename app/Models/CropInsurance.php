<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropInsurance extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $fillable = [
    //     'user_id', 'crop', 'area_unit', 'area', 'insurance_type', 'company',
    //     'benchmark', 'benchmark_percent', 'sum_insured_100_percent',
    //     'sum_insured', 'premium_price', 'district_id', 'tehsil_id', 'year',
    //     'compensation', 'status'
    // ];

    public function user()
    {
        return $this->belongsTo(Farmer::class, 'user_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function tehsil()
    {
        return $this->belongsTo(Tehsil::class, 'tehsil_id');
    }
}
