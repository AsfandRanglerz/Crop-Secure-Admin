<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnsuredCropName extends Model
{
    use HasFactory;
    protected $table = 'ensured_crop_name';
    protected $guarded=[];

    // public function crop_types()
    // {
    //     return $this->hasMany(EnsuredCropType::class, 'crop_name_id');
    // }


    public function insuranceSubTypes()
{
    return $this->hasMany(InsuranceSubType::class, 'name', 'id');
}

}
