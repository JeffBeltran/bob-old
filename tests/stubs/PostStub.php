<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class PostStub extends Eloquent
{
    protected $connection = 'testbench';
    public $table = 'posts';

    public function comments()
    {
        return $this->hasMany(CommentStub::class);
    }
}