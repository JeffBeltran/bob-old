<?php

use Faker\Generator as Faker;

$factory->define(PostStub::class, function (Faker $faker) {
    return [
        'title' => $faker->words(3, true)
    ];
});
