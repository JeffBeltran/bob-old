<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class CommentStub extends Eloquent
{
    protected $connection = 'testbench';
    public $table = 'comments';
}