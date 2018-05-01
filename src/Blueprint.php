<?php

namespace JeffBeltran\Bob;

interface Blueprint
{
    /**
     * Apply a given filter value to the builder instance. Due to Scout, builder instance can vary
     *
     * @param mixed $builder
     * @param mixed $value
     * @return mixed $builder
     */
    public static function apply($builder, $value);
}
