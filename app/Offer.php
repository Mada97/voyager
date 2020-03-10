<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'user_id', 'trip_id', 'offer_price', 'number_of_seats_needed'
    ];

    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
