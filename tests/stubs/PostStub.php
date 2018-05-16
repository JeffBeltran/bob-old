<?php

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model as Eloquent;

class PostStub extends Eloquent
{
    protected $connection = 'testbench';
    public $table = 'posts';

    use Searchable;

    public function comments()
    {
        return $this->hasMany(CommentStub::class);
    }
}