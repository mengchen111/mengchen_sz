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

$factory->define(App\Models\WxOrder::class, function (Faker\Generator $faker) {
    $userId = $faker->randomElement([10, 11, 12]);
    $id = rand(1, 4);
    $rule = \App\Models\WxTopUpRule::find($id);
    $time = \Carbon\Carbon::parse('-1 month')->toDateTimeString();
    return [
        'user_id' => $userId,
        'wx_top_up_rule_id' => $rule->id,
        'out_trade_no' => str_random(),
        'total_fee' => $rule->price * 100,
        'body' => $rule->remark,
        'order_status' => 2,
        'item_delivery_status' => 1,
        'paid_at' => $time,
    ];
});