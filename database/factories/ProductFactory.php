<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->text(),
        'category_id' => factory('App\Category')->create()->id,
        'created_by' => factory('App\User')->create()->id,
    ];
});