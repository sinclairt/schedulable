<?php

class Plan extends \Illuminate\Database\Eloquent\Model implements \Wtbi\Schedulable\Contracts\IsSchedulable
{
    use \Wtbi\Schedulable\Traits\IsSchedulable;

    protected $fillable = ['name'];
}