<?php

class Plan extends \Illuminate\Database\Eloquent\Model implements \Sinclair\Schedulable\Contracts\IsSchedulable
{
    use \Sinclair\Schedulable\Traits\IsSchedulable;

    protected $fillable = ['name'];
}