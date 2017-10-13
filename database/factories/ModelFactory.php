<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'account' => $faker->unique()->firstName,
        'password' => $password ?: $password = bcrypt('password'),
        'group_id' => 2,
        'parent_id' => 1,
    ];
});

$factory->define(App\Models\TopUpPlayer::class, function (Faker\Generator $faker) {
    return [
        'provider_id' => 1,
        'player' => 10000,
        'type' => 1,
        'amount' => $faker->numberBetween(1, 100),
        'created_at' => \Carbon\Carbon::now(),
    ];
});
