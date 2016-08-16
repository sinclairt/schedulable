<?php

if ( !function_exists('schedule') )
{
    /**
     * @param  object|null $object
     *
     * @return \Wtbi\Schedulable\Services\ScheduleFactory
     */
    function schedule( $object = null )
    {
        return app('ScheduleFactory', compact('object'));
    }
}