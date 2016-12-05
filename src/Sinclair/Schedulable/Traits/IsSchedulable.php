<?php

namespace Sinclair\Schedulable\Traits;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sinclair\Schedulable\Models\Schedule;

/**
 * Class IsSchedulable
 * @package Sinclair\Schedulable\Traits
 * @method static dueOn( Carbon $dt, $active = true )
 * @method static isNow()
 * @method static isMinutely()
 * @method static isHourly()
 * @method static isDaily()
 * @method static isWeekly()
 * @method static isMonthly()
 * @method static isAnnually()
 * @method static isQuarterly()
 * @method static isAdhoc()
 * @method static between( Carbon $dtFrom, Carbon $dtTo = null, $active = true )
 * @method static day( Carbon $dt )
 * @method static dayBetween( Carbon $dtFrom, Carbon $dtTo = null )
 * @method static dayOfMonth( int $day )
 * @method static dayOfMonthBetween( Carbon $dtFrom, Carbon $dtTo = null )
 * @method static dayOfWeek( int $day )
 * @method static dayOfWeekBetween( Carbon $dtFrom, Carbon $dtTo = null )
 * @method static lastOfMonth( Carbon $dt )
 * @method static month( int $month )
 * @method static monthBetween( int $from, int $to )
 * @method static year( int $year )
 * @method static yearBetween( int $from, int $to )
 * @method static hour( int $hour )
 * @method static hourBetween( int $from, int $to )
 * @method static minute( int $minute )
 * @method static minuteBetween( int $from, int $to )
 * @method static isActive( Carbon $dt = null )
 * @method static isActiveBetween( Carbon $dtFrom, Carbon $dtTo = null )
 * @method static isExpired( Carbon $dt = null )
 */
trait IsSchedulable
{
    /**
     * Get the schedule.
     *
     * @return Schedule|\Sinclair\Schedulable\Contracts\IsSchedulable
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
        return $this->schedule->next($dt, $nth);
    }

    /**
     * @param Carbon|null $dt
     * @param int $nth
     *
     * @return Carbon
     */
    public function previous( Carbon $dt = null, $nth = 0 )
    {
        return $this->schedule->previous($dt, $nth);
    }

    /**
     * @param Carbon|null $dt
     *
     * @return bool
     */
    public function isDue( Carbon $dt = null )
    {
        return $this->schedule->isDue($dt);
    }

    /**
     * @param int $total
     *
     * @return Collection
     */
    public function nextRunDates( $total )
    {
        return $this->schedule->nextRunDates($total);
    }

    /**
     * @param int $total
     *
     * @return Collection
     */
    public function previousRunDates( $total )
    {
        return $this->schedule->previousRunDates($total);
    }

    /**
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     * @param bool $active
     *
     * @return Collection
     */
    public function runDatesBetween( Carbon $dtFrom, Carbon $dtTo = null, $active = true )
    {
        return $this->schedule->runDatesBetween($dtFrom, $dtTo, $active);
    }

    /**
     * @return bool
     */
    public function hasRun()
    {
        $schedule = $this->schedule;

        $schedule->last_ran_at = Carbon::now()
                                       ->toDateTimeString();

        $schedule->next_runs_at = $schedule->next()
                                           ->toDateTimeString();

        return $schedule->save();
    }

    /**
     * @param $query
     * @param Carbon $dt
     * @param bool $active
     *
     * @return mixed
     */
    public function scopeDueOn( $query, Carbon $dt, $active = true )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dt, $active )
        {
            $query->dueOn($dt, $active);
        });
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsNow( $query )
    {
        return $query->dueOn(Carbon::now());
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsMinutely( $query )
    {
        return $query->whereHas('schedule', function ( $query )
        {
            $query->isMinutely();

        });
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsHourly( $query )
    {
        return $query->whereHas('schedule', function ( $query )
        {
            return $query->isHourly();
        });
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsDaily( $query )
    {
        return $query->whereHas('schedule', function ( $query )
        {
            return $query->isDaily();
        });
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsWeekly( $query )
    {
        return $query->whereHas('schedule', function ( $query )
        {
            return $query->isWeekly();
        });
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsMonthly( $query )
    {
        return $query->whereHas('schedule', function ( $query )
        {
            return $query->isMonthly();
        });
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsAnnually( $query )
    {
        return $query->whereHas('schedule', function ( $query )
        {
            return $query->isAnnually();
        });
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsQuarterly( $query )
    {
        return $query->whereHas('schedule', function ( $query )
        {
            return $query->isQuarterly();
        });
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsAdhoc( $query )
    {
        return $query->whereHas('schedule', function ( $query )
        {
            return $query->isAdhoc();
        });
    }

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     * @param bool $active
     *
     * @return mixed
     */
    public function scopeBetween( $query, Carbon $dtFrom, Carbon $dtTo = null, $active = true )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dtFrom, $dtTo, $active )
        {
            $query->between($dtFrom, $dtTo, $active);
        });
    }

    /**
     * @param $query
     * @param Carbon $dt
     *
     * @return mixed
     */
    public function scopeDay( $query, Carbon $dt )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dt )
        {
            $query->day($dt);
        });
    }

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     *
     * @return mixed
     */
    public function scopeDayBetween( $query, Carbon $dtFrom, Carbon $dtTo = null )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dtFrom, $dtTo )
        {
            $query->dayBetween($dtFrom, $dtTo);
        });
    }

    /**
     * @param $query
     * @param int $day
     *
     * @return mixed
     */
    public function scopeDayOfMonth( $query, int $day )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $day )
        {
            $query->dayOfMonth($day);
        });
    }

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     *
     * @return mixed
     */
    public function scopeDayOfMonthBetween( $query, Carbon $dtFrom, Carbon $dtTo = null )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dtFrom, $dtTo )
        {
            $query->dayOfMonthBetween($dtFrom, $dtTo);
        });
    }

    /**
     * @param $query
     * @param int $day
     *
     * @return mixed
     */
    public function scopeDayOfWeek( $query, int $day )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $day )
        {
            $query->dayOfWeek($day);
        });
    }

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     *
     * @return mixed
     */
    public function scopeDayOfWeekBetween( $query, Carbon $dtFrom, Carbon $dtTo = null )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dtFrom, $dtTo )
        {
            $query->dayOfWeekBetween($dtFrom, $dtTo);
        });
    }

    /**
     * @param $query
     * @param Carbon $dt
     *
     * @return mixed
     */
    public function scopeLastOfMonth( $query, Carbon $dt )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dt )
        {
            $query->lastOfMonth($dt);
        });
    }

    /**
     * @param $query
     * @param int $month
     *
     * @return mixed
     */
    public function scopeMonth( $query, int $month )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $month )
        {
            $query->month($month);
        });
    }

    /**
     * @param $query
     * @param int $from
     * @param int $to
     *
     * @return mixed
     */
    public function scopeMonthBetween( $query, int $from, int $to )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $from, $to )
        {
            $query->monthBetween($from, $to);
        });
    }

    /**
     * @param $query
     * @param int $year
     *
     * @return mixed
     */
    public function scopeYear( $query, int $year )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $year )
        {
            $query->year($year);
        });
    }

    /**
     * @param $query
     * @param int $from
     * @param int $to
     *
     * @return mixed
     */
    public function scopeYearBetween( $query, int $from, int $to )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $from, $to )
        {
            $query->yearBetween($from, $to);
        });
    }

    /**
     * @param $query
     * @param int $hour
     *
     * @return mixed
     */
    public function scopeHour( $query, int $hour )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $hour )
        {
            $query->hour($hour);
        });
    }

    /**
     * @param $query
     * @param int $from
     * @param int $to
     *
     * @return mixed
     */
    public function scopeHourBetween( $query, int $from, int $to )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $from, $to )
        {
            $query->hourBetween($from, $to);
        });
    }

    /**
     * @param $query
     * @param int $minute
     *
     * @return mixed
     */
    public function scopeMinute( $query, int $minute )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $minute )
        {
            $query->minute($minute);
        });
    }

    /**
     * @param $query
     * @param int $from
     * @param int $to
     *
     * @return mixed
     */
    public function scopeMinuteBetween( $query, int $from, int $to )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $from, $to )
        {
            $query->minuteBetween($from, $to);
        });
    }

    /**
     * @param $query
     * @param Carbon|null $dt
     *
     * @return mixed
     */
    public function scopeIsActive( $query, Carbon $dt = null )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dt )
        {
            $query->isActive($dt);
        });
    }

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     *
     * @return mixed
     */
    public function scopeIsActiveBetween( $query, Carbon $dtFrom, Carbon $dtTo = null )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dtFrom, $dtTo )
        {
            $query->isActiveBetween($dtFrom, $dtTo);
        });
    }

    /**
     * @param $query
     * @param Carbon|null $dt
     *
     * @return mixed
     */
    public function scopeIsExpired( $query, Carbon $dt = null )
    {
        return $query->whereHas('schedule', function ( $query ) use ( $dt )
        {
            $query->isExpired($dt);
        });
    }
}
