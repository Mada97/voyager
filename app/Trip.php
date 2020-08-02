<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'user_id',
        'from',
        'to',
        'car_model',
        'price_per_passenger',
        'number_of_empty_seats',
        'departure_date',
        'description'
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }
    public function offers() {
        return $this->hasMany('App\Offer');
    }
}
