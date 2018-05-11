<?php

namespace App\Blueprints\PostStub;

use JeffBeltran\Bob\Blueprint;

class Sort implements Blueprint
{
    /**
     * Apply a given search value to the builder instance. Due to Scout, builder instance can vary
     *
     * @param mixed $builder
     * @param mixed $value
     * @return mixed $builder
     */
    public static function apply($builder, $value)
    {
        $sortBy = explode(',', $value);
        return $builder->orderBy($sortBy[0], $sortBy[1]);
    }
}