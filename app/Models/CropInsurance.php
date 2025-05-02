<?php

namespace App\Models;

use App\Models\InsuranceType;
use App\Models\InsuranceCompany;
use App\Models\InsuranceSubType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CropInsurance extends Model
{
    use HasFactory;
    protected $guarded = [];
 
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

    public function companys()
    {
        return $this->belongsTo(InsuranceCompany::class, 'company');
    }

    public function insuranceType()
    {
        return $this->belongsTo(InsuranceType::class, 'insurance_type');
    }

    public function insuranceSubType()
{
    return $this->belongsTo(InsuranceSubType::class, 'sub_type_id'); // adjust if needed
}

}
