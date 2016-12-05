<?php

namespace Sinclair\Schedulable\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sinclair\Schedulable\Contracts\Schedule as ScheduleInterface;

/**
 * Interface IsSchedulable
 * @package Sinclair\Schedulable\Contracts
 * @property Schedule $schedule
 * @method static dueOn(Carbon $dt, $active = true )
 * @method static isNow()
 * @method static isMinutely()
 * @method static isHourly()
 * @method static isDaily()
 * @method static isWeekly()
 * @method static isMonthly()
 * @method static isAnnually()
 * @method static isQuarterly()
 * @method static isAdhoc()
 * @method static between(Carbon $dtFrom, Carbon $dtTo = null, $active = true )
 * @method static day(Carbon $dt )
 * @method static dayBetween( Carbon $dtFrom, Carbon $dtTo = null )
 * @method static dayOfMonth( int $day )
 * @method static dayOfMonthBetween(Carbon $dtFrom, Carbon $dtTo = null )
 * @method static dayOfWeek(int $day )
 * @method static dayOfWeekBetween(Carbon $dtFrom, Carbon $dtTo = null )
 * @method static lastOfMonth(Carbon $dt )
 * @method static month(int $month )
 * @method static monthBetween( int $from, int $to )
 * @method static year(int $year )
 * @method static yearBetween( int $from, int $to )
 * @method static hour( int $hour )
 * @method static hourBetween( int $from, int $to )
 * @method static minute( int $minute )
 * @method static minuteBetween( int $from, int $to )
 * @method static isActive( Carbon $dt = null )
 * @method static isActiveBetween( Carbon $dtFrom, Carbon $dtTo = null )
 * @method static isExpired( Carbon $dt = null )
 */
interface IsSchedulable
{
    /**
     * Get the schedule.
     *
     * @return ScheduleInterface|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
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
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     * @param bool $active
     *
     * @return Collection
     */
    public function runDatesBetween( Carbon $dtFrom, Carbon $dtTo = null, $active = true );

    /**
     * @return bool
     */
    public function hasRun();

    /**
     * @param $query
     * @param Carbon $dt
     * @param bool $active
     *
     * @return mixed
     */
    public function scopeDueOn( $query, Carbon $dt, $active = true );

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsNow( $query );

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsMinutely( $query );

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsHourly( $query );

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsDaily( $query );

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsWeekly( $query );

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsMonthly( $query );

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsAnnually( $query );

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsQuarterly( $query );

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsAdhoc( $query );

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     * @param bool $active
     *
     * @return mixed
     */
    public function scopeBetween( $query, Carbon $dtFrom, Carbon $dtTo = null, $active = true );

    /**
     * @param $query
     * @param Carbon $dt
     *
     * @return mixed
     */
    public function scopeDay( $query, Carbon $dt );

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     *
     * @return mixed
     */
    public function scopeDayBetween( $query, Carbon $dtFrom, Carbon $dtTo = null );

    /**
     * @param $query
     * @param int $day
     *
     * @return mixed
     */
    public function scopeDayOfMonth( $query, int $day );

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     *
     * @return mixed
     */
    public function scopeDayOfMonthBetween( $query, Carbon $dtFrom, Carbon $dtTo = null );

    /**
     * @param $query
     * @param int $day
     *
     * @return mixed
     */
    public function scopeDayOfWeek( $query, int $day );

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     *
     * @return mixed
     */
    public function scopeDayOfWeekBetween( $query, Carbon $dtFrom, Carbon $dtTo = null );

    /**
     * @param $query
     * @param Carbon $dt
     *
     * @return mixed
     */
    public function scopeLastOfMonth( $query, Carbon $dt );

    /**
     * @param $query
     * @param int $month
     *
     * @return mixed
     */
    public function scopeMonth( $query, int $month );

    /**
     * @param $query
     * @param int $from
     * @param int $to
     *
     * @return mixed
     */
    public function scopeMonthBetween( $query, int $from, int $to );

    /**
     * @param $query
     * @param int $year
     *
     * @return mixed
     */
    public function scopeYear( $query, int $year );

    /**
     * @param $query
     * @param int $from
     * @param int $to
     *
     * @return mixed
     */
    public function scopeYearBetween( $query, int $from, int $to );

    /**
     * @param $query
     * @param int $hour
     *
     * @return mixed
     */
    public function scopeHour( $query, int $hour );

    /**
     * @param $query
     * @param int $from
     * @param int $to
     *
     * @return mixed
     */
    public function scopeHourBetween( $query, int $from, int $to );

    /**
     * @param $query
     * @param int $minute
     *
     * @return mixed
     */
    public function scopeMinute( $query, int $minute );

    /**
     * @param $query
     * @param int $from
     * @param int $to
     *
     * @return mixed
     */
    public function scopeMinuteBetween( $query, int $from, int $to );

    /**
     * @param $query
     * @param Carbon|null $dt
     *
     * @return mixed
     */
    public function scopeIsActive( $query, Carbon $dt = null );

    /**
     * @param $query
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     *
     * @return mixed
     */
    public function scopeIsActiveBetween( $query, Carbon $dtFrom, Carbon $dtTo = null );

    /**
     * @param $query
     * @param Carbon|null $dt
     *
     * @return mixed
     */
    public function scopeIsExpired( $query, Carbon $dt = null );
}