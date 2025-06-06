<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceType extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insuranceSubTypes()
    {
        return $this->hasMany(InsuranceSubType::class, 'incurance_type_id');
    }

    
}
