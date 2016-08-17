<?php

namespace Wtbi\Schedulable\Contracts;

/**
 * Class Builder
 * @package Wtbi\Schedulable
 */
interface ScheduleFactory
{
    /**
     * @param Schedule $schedule
     *
     * @return ScheduleFactory
     */
    public function setSchedule( Schedule $schedule );

    /**
     * @return Schedule
     */
    public function getSchedule();

    /**
     * @param null $object
     *
     * @return static
     */
    public function setObject( $object );

    /**
     * @return null|IsSchedulable
     */
    public function getObject();

    /**
     * @return static
     */
    public function save();

    /**
     * @return \Wtbi\Schedulable\Services\ScheduleFactory
     */
    public function load();

    /**
     * @param null $schedule
     *
     * @return ScheduleFactory
     */
    public function loadFromSchedule( $schedule = null );

    /**
     * @param string $expression
     *
     * @return static
     */
    public function loadFromCron( string $expression );

    /**
     * @return mixed
     */
    public function refresh();

    /**
     * @return ScheduleFactory
     */
    public function resetSchedule();
}