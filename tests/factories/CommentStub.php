<?php

use Faker\Generator as Faker;

$factory->define(CommentStub::class, function (Faker $faker) {
    return [
        'body' => $faker->sentence()
    ];
});
