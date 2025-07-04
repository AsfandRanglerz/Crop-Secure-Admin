<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class AuthorizedDealer extends Model
{
    use HasApiTokens, HasFactory;
    protected $guarded = [];

    public function dealerItems()
    {
        return $this->hasMany(DealerItem::class);
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
    public function setFatherNameAttribute($value)
    {
        $this->attributes['father_name'] = collect(explode(' ', strtolower($value)))
            ->map(fn($word) => ucfirst($word))
            ->implode(' ');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
}
