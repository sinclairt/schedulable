<?php

namespace Wtbi\Schedulable\Contracts;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class Schedule
 * @package Wtbi\Schedulable\Models
 * @property int $id
 * @property string $schedulable_type
 * @property int $schedulable_id
 * @property int $minute
 * @property int $hour
 * @property int $day_of_week
 * @property int $day_of_month
 * @property int $month_of_year
 * @property int $year
 * @property bool $is_last_day_of_month
 * @property bool $is_adhoc
 * @property bool $is_minutely
 * @property bool $is_hourly
 * @property bool $is_daily
 * @property bool $is_weekly
 * @property bool $is_monthly
 * @property bool $is_annually
 * @property bool $is_quarterly
 * @property int $frequency_n
 * @property Carbon $starts_at
 * @property Carbon $expires_at
 * @property Carbon $last_ran_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @mixin \Eloquent
 */
interface Schedule
{
    /**
     * Get all of the owning schedulable models.
     */
    public function schedulable();

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
     * @param Carbon|null $dt
     *
     * @return bool
     */
    public function isDue( Carbon $dt = null );

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