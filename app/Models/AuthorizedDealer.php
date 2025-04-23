<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class AuthorizedDealer extends Model
{
    use HasApiTokens, HasFactory;
    protected $guarded=[];

    public function dealerItems()
    {
        return $this->hasMany(DealerItem::class);
    }
}
