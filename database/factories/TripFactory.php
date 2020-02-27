<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Trip;
use Faker\Generator as Faker;

$factory->define(Trip::class, function (Faker $faker) {
    return [
        'user_id' => 3,
        'from' => $faker->city,
        'to' => $faker->city,
        'car_model' => "Toyota",
        'price_per_passenger' => $faker->numberBetween($min = 5,$max = 100),
        'number_of_empty_seats' => $faker->numberBetween($min = 1, $max = 4),
        'departure_date' => $faker->date,
        'description' => $faker->sentence(6),
    ];
});
