<?php

namespace Wtbi\Schedulable\Traits;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Wtbi\Schedulable\Models\Schedule;

/**
 * Class IsSchedulable
 * @package Wtbi\Schedulable\Traits
 */
trait IsSchedulable
{
    /**
     * Get the schedule.
     *
     * @return Schedule
     */
    public function schedule()
    {
        return $this->morphOne(Schedule::class, 'schedulable');
    }

    /**
     * @return bool
     */
    public function hasSchedule()
    {
        return !is_null($this->schedule);
    }

    /**
     * @param Carbon|null $dt
     * @param int $nth
     *
     * @return Carbon
     */
    public function next( Carbon $dt = null, $nth = 0 )
    {
        return $this->schedule()
                    ->next($dt, $nth);
    }

    /**
     * @param Carbon|null $dt
     * @param int $nth
     *
     * @return Carbon
     */
    public function previous( Carbon $dt = null, $nth = 0 )
    {
        return $this->schedule()
                    ->previous($dt, $nth);
    }

    /**
     * @param Carbon|null $dt
     *
     * @return bool
     */
    public function isDue( Carbon $dt = null )
    {
        return $this->schedule()
                    ->isDue($dt);
    }

    /**
     * @param int $total
     *
     * @return Collection
     */
    public function nextRunDates( $total )
    {
        return $this->schedule()
                    ->nextRunDates($total);
    }

    /**
     * @param int $total
     *
     * @return Collection
     */
    public function previousRunDates( $total )
    {
        return $this->schedule()
                    ->previousRunDates($total);
    }

    /**
     * @return bool
     */
    public function hasRun()
    {
        $schedule = $this->schedule();

        $schedule->last_ran_at = Carbon::now()
                                       ->toDateTimeString();

        return $schedule->save();
    }
}
