<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceProductClaim extends Model
{
    use HasFactory;

    protected $guarded = [];
    //      protected $fillable = [
    //     'insurance_id',
    //     'dealer_id',
    //     'item_id',
    //     'price',
    //     'products',
    //     'state',
    //     'address',
    //     'city',
    //     'delivery_status',
    // ];


    public function insurance()
    {
        return $this->belongsTo(InsuranceHistory::class, 'insurance_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function dealer()
    {
        return $this->belongsTo(AuthorizedDealer::class, 'dealer_id');
    }

    public function address()
    {
        return $this->belongsTo(InsuranceClaimAddress::class, 'address_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\Farmer::class, 'user_id');
    }
}
