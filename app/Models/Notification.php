<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $guarded=[];
     protected $fillable = [
    'user_type',
    'message',
    
    'is_sent',
];
public function getUserTypeAttribute($value)
{
    // Replace all non-alphabetic characters (e.g., _, -, ,) with a space
    $cleaned = preg_replace('/[^a-zA-Z0-9]+/', ' ', strtolower($value));

    // Capitalize each word and return as a single string
    return collect(explode(' ', trim($cleaned)))
        ->filter()
        ->map(fn($word) => ucfirst($word))
        ->implode(' ');
}



    public function notifytables()
{
    return $this->hasMany(NotificationTarget::class, 'notification_id');
}

    public function targets()
{
    return $this->hasMany(NotificationTarget::class);
}
    public function notifytable(){
        return $this->morphMany(NotificationTarget::class,'targetable');
    }
}
