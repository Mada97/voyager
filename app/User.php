<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone_number', 'gender', 'date_of_birth', 'avatar'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function notifications()
    {
        // Return sorted notifications
        return $this->morphMany(Notification::class, "notifiable")
        // ->whereNull("read_at")
        // ->orderBy("type", "asc")
        ->orderBy("created_at", "desc");
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function offers() {
        return $this->hasMany('App\Offer');
    }

    public function trips() {
        return $this->hasMany('App\Trip');
    }

    public function rate() {
        return $this->hasMany('App\Rate');
    }
}
