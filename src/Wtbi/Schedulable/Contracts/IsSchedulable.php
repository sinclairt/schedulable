<?php

namespace Wtbi\Schedulable\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Wtbi\Schedulable\Contracts\Schedule as ScheduleInterface;

/**
 * Interface IsSchedulable
 * @package Wtbi\Schedulable\Contracts
 * @property Schedule $schedule
 */
interface IsSchedulable
{
    /**
     * Get the schedule.
     *
     * @return ScheduleInterface
     */
    public function schedule();

    /**
     * @return bool
     */
    public function hasSchedule();

    /**
     * @param Carbon|null $dt
     * @param int $nth
     *
     * @return Carbon
     */
    public function next( Carbon $dt = null, $nth = 0 );

    /**
     * @param Carbon|null $dt
     * @param int $nth
     *
     * @return Carbon
     */
    public function previous( Carbon $dt = null, $nth = 0 );

    /**
     * @param Carbon|null $dt
     *
     * @return bool
     */
    public function isDue( Carbon $dt = null );

    /**
     * @param int $total
     *
     * @return Collection
     */
    public function nextRunDates( $total );

    /**
     * @param int $total
     *
     * @return Collection
     */
    public function previousRunDates( $total );

    /**
     * @return bool
     */
    public function hasRun();
}