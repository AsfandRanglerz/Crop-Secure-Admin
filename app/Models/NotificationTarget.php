<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTarget extends Model
{
    protected $fillable = ['notification_id', 'targetable_id', 'targetable_type'];
    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function targetable()
    {
        return $this->morphTo();
    }
    
}