<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceSubType extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_name', 'id'); 
    }

    public function tehsil()
    {
        return $this->belongsTo(Tehsil::class, 'tehsil_id', 'id');
    }

    public function crop()
    {
        return $this->belongsTo(EnsuredCropName::class, 'crop_name_id', 'id');
    }


}
