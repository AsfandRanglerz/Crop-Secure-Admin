<?php

namespace App\Models;

use App\Models\DealerItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductSelection extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function dealerItem()
{
    return $this->belongsTo(DealerItem::class);
}

}
