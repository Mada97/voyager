<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = ['rating', 'action_user_id', 'rated_user_id'];
}
