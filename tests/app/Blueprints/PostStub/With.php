<?php

namespace App\Blueprints\PostStub;

use JeffBeltran\Bob\Blueprint;

class With implements Blueprint
{
    /**
     * Load listed relations for models if eloquent builder
     *
     * @param mixed $builder
     * @param mixed $value
     * @return mixed $builder
     */
    public static function apply($builder, $value)
    {
        if (get_class($builder) == 'Illuminate\Database\Eloquent\Builder') {
            $withArray = explode(',', $value);
            return $builder->with($withArray);
        } else {
            return $builder;
        }
    }
}
