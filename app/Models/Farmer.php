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
    
    protected $guarded=[];

    protected $dates = ['dob'];

    public function selectedProducts()
{
    return $this->hasMany(ProductSelection::class);
}

}
