<?php

namespace Wtbi\Schedulable\Contracts;

/**
 * Class Builder
 * @package Wtbi\Schedulable
 */
interface ScheduleFactory
{
    /**
     * @param null $object
     *
     * @return static
     */
    public function setObject( $object );

    /**
     * @return static
     */
    public function save();

    /**
     * @return \Wtbi\Schedulable\ScheduleFactory
     */
    public function load();

    /**
     * @return mixed
     */
    public function refresh();

    /**
     * @param string $expression
     *
     * @return static
     */
    public function loadFromCron( string $expression );
}