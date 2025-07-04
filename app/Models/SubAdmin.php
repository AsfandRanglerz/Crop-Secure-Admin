<?php

namespace App\Models;

// use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class SubAdmin extends Authenticatable implements AuthenticatableContract
{
    use HasApiTokens, HasFactory;
    protected $guard = 'subadmin';
    protected $guarded = [];
    // protected $hidden = ['password', 'remember_token'];

    public function permissions()
    {
        return $this->hasMany(SubAdminPermission::class);
    }

    public function side_menu()
    {
        return $this->belongsTo(SideMenu::class, 'side_menu_id');
    }

    public function setEmailAttribute($value)
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
