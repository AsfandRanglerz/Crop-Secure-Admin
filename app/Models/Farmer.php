<?php

namespace App\Models;

use App\Models\ProductSelection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Farmer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $dates = ['dob'];
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
    public function setFnameAttribute($value)
    {
        $this->attributes['fname'] = collect(explode(' ', strtolower($value)))
            ->map(fn($word) => ucfirst($word))
            ->implode(' ');
    }
    public function getNameAttribute($value)
    {
        // Replace all non-alphabetic characters (e.g., _, -, ,) with a space
        $cleaned = preg_replace('/[^a-zA-Z0-9]+/', ' ', strtolower($value));

        // Capitalize each word and return as a single string
        return collect(explode(' ', trim($cleaned)))
            ->filter()
            ->map(fn($word) => ucfirst($word))
            ->implode(' ');
    }

    public function selectedProducts()
    {
        return $this->hasMany(ProductSelection::class);
    }
    public function notifytable()
    {
        return $this->morphMany(NotificationTarget::class, 'targetable');
    }

    public function notifications()
    {
        return $this->morphMany(\App\Models\NotificationTarget::class, 'targetable');
    }

    public function bankDetail()
{
    return $this->hasOne(\App\Models\UserBankDetail::class, 'user_id');
}

}
