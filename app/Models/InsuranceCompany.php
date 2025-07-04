<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceCompany extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insuranceTypes()
    {
        return $this->hasMany(InsuranceType::class, 'incurance_company_id');
    }

    public function setEmailAttribute($value) //set lowercase
    {
        $this->attributes['email'] = strtolower($value);
    }
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = collect(explode(' ', strtolower($value)))
            ->map(fn($word) => ucfirst($word))
            ->implode(' ');
    }
}
