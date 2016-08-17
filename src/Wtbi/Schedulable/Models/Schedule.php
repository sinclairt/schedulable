<?php

namespace Wtbi\Schedulable\Models;

use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wtbi\Schedulable\Contracts\Schedule as ScheduleInterface;

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
class Schedule extends Model implements ScheduleInterface
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schedulable_type',
        'schedulable_id',
        'minute',
        'hour',
        'day_of_week',
        'day_of_month',
        'month_of_year',
        'year',
        'is_last_day_of_month',
        'is_adhoc',
        'is_minutely',
        'is_hourly',
        'is_daily',
        'is_weekly',
        'is_monthly',
        'is_annually',
        'frequency_n',
        'starts_at',
        'expires_at',
        'last_ran_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [ ];

    /**
     * The dates that are returned as Carbon objects
     *
     * @var array
     */
    protected $dates = [
        'starts_at',
        'expires_at',
        'last_ran_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get all of the owning schedulable models.
     */
    public function schedulable()
    {
        return $this->morphTo();
    }

    /**
     * @param Carbon|null $dt
     * @param int $nth
     *
     * @return Carbon
     */
    public function next( Carbon $dt = null, $nth = 0 )
    {
        return Carbon::instance(CronExpression::factory($this->getCronExpression())
                                              ->getNextRunDate($dt, $nth));
    }

    /**
     * @param Carbon|null $dt
     * @param int $nth
     *
     * @return Carbon
     */
    public function previous( Carbon $dt = null, $nth = 0 )
    {
        return Carbon::instance(CronExpression::factory($this->getCronExpression())
                                              ->getPreviousRunDate($dt, $nth));
    }

    /**
     * @param int $total
     *
     * @return Collection
     */
    public function nextRunDates( $total )
    {
        return collect(CronExpression::factory($this->getCronExpression())
                                     ->getMultipleRunDates($total));
    }

    /**
     * @param int $total
     *
     * @return Collection
     */
    public function previousRunDates( $total )
    {
        return collect(CronExpression::factory($this->getCronExpression())
                                     ->getMultipleRunDates($total, 'now', true));
    }

    /**
     * @param Carbon|null $dt
     *
     * @return bool
     */
    public function isDue( Carbon $dt = null )
    {
        return CronExpression::factory($this->getCronExpression())
                             ->isDue($dt);
    }

    /**
     * @return string
     */
    protected function getCronExpression()
    {
        return implode(' ', [ $this->minute or '*', $this->hour or '*', $this->day_of_month or '*', $this->month_of_year or '*', $this->day_of_week or '*', $this->year or '*' ]);
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
        $query = $query->day($dt)
                       ->month($dt->month)
                       ->hour($dt->hour)
                       ->minute($dt->minute)
                       ->year($dt->year);

        return $active ? $query->active($dt) : $query;
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
        return $query->where('is_minutely', 1);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsHourly( $query )
    {
        return $query->where('is_hourly', 1);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsDaily( $query )
    {
        return $query->where('is_daily', 1);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsWeekly( $query )
    {
        return $query->where('is_weekly', 1);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsMonthly( $query )
    {
        return $query->where('is_monthly', 1);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsAnnually( $query )
    {
        return $query->where('is_annual', 1);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsQuarterly( $query )
    {
        return $query->where('is_quarterly', 1);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsAdhoc( $query )
    {
        return $query->where('is_adhoc', 1);
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
        $query = $query->dayBetween($dtFrom, $dtTo)
                       ->monthBetween($dtFrom->month, $dtTo->month)
                       ->hourBetween($dtFrom->hour, $dtTo->hour)
                       ->minuteBetween($dtFrom->minute, $dtTo->minute)
                       ->yearBetween($dtFrom->year, $dtTo->year);

        return $active ? $query->activeBetween($dtFrom, $dtTo) : $query;
    }

    /**
     * @param $query
     * @param Carbon $dt
     *
     * @return mixed
     */
    public function scopeDay( $query, Carbon $dt )
    {
        return $query->where(function ( $query ) use ( $dt )
        {
            $query->where(function ( $query ) use ( $dt )
            {
                $query->dayOfWeek($dt->dayOfWeek);
            })
                  ->orWhere(function ( $query ) use ( $dt )
                  {
                      $query->dayOfMonth($dt->day);
                  })
                  ->orWhere(function ( $query ) use ( $dt )
                  {
                      $query->lastOfMonth($dt);
                  });
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
        return $query->where(function ( $query ) use ( $dtFrom, $dtTo )
        {
            $query->where(function ( $query ) use ( $dtFrom, $dtTo )
            {
                $query->dayOfWeekBetween($dtFrom, $dtTo);
            })
                  ->orWhere(function ( $query ) use ( $dtFrom, $dtTo )
                  {
                      $query->dayOfMonthBetween($dtFrom, $dtTo);
                  });
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
        // where day of month == $day OR if schedule is not monthly/annual/adhoc/quarterly then where day of month is null
        return $query->where(function ( $query ) use ( $day )
        {
            $query->where(function ( $query )
            {
                $query->whereNull('day_of_month')
                      ->where(function ( $query )
                      {
                          $query->where('is_monthly', 0)
                                ->where('is_annually', 0)
                                ->where('is_annual', 0)
                                ->where('is_quarterly', 0);
                      });
            })
                  ->orWhere(function ( $query ) use ( $day )
                  {

                      $query->where('day_of_month', $day)
                            ->isMonthly();
                  });
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
        // where day of month is between $from and $to OR if schedule is not monthly or annual then where day of month is null
        return $query->where(function ( $query ) use ( $dtFrom, $dtTo )
        {
            $query->where(function ( $query ) use ( $dtFrom, $dtTo )
            {
                $query->whereBetween('day_of_month', [ $dtFrom->day, $dtTo->day ])
                      ->isMonthly();
            })
                  ->orWhere(function ( $query ) use ( $dtFrom, $dtTo )
                  {
                      $query->whereNull('day_of_month')
                            ->where(function ( $query )
                            {
                                $query->where('is_monthly', 0)
                                      ->where('is_annually', 0)
                                      ->where('is_annual', 0)
                                      ->where('is_quarterly', 0);
                            });
                  });
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
        // where day of week == $day OR if schedule is not weekly then where day of week is null
        return $query->where(function ( $query ) use ( $day )
        {
            $query->where(function ( $query ) use ( $day )
            {
                $query->where('day_of_week', $day)
                      ->isWeekly();
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('day_of_week')
                            ->where('is_weekly', 0);
                  });
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
        // where day of week is between $from and $to OR if schedule is not weekly then where day of week is null
        return $query->where(function ( $query ) use ( $dtFrom, $dtTo )
        {
            $query->where(function ( $query ) use ( $dtFrom, $dtTo )
            {
                $query->whereBetween('day_of_week', [ $dtFrom->dayOfWeek, $dtTo->dayOfWeek ])
                      ->isWeekly();
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('day_of_week')
                            ->where('is_weekly', 0);
                  });
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
        // is the day is the last of month
        // and the schedule is monthly, and the schedule runs on the last day of the month OR the schedule is not monthly/annual/adhoc then last day of month is null

        if ( $dt->daysInMonth == $dt->day )
            return $query->where(function ( $query ) use ( $dt )
            {
                $query->where(function ( $query ) use ( $dt )
                {
                    $query->isMonthly()
                          ->isLastDayOfMonth();
                })
                      ->orWhere(function ( $query )
                      {
                          $query->whereNull('is_last_day_of_month')
                                ->where(function ( $query )
                                {
                                    $query->where('is_monthly', 0)
                                          ->where('is_annually', 0)
                                          ->where('is_adhoc', 0)
                                          ->where('is_quarterly', 0);
                                });
                      });
            });

        return $query;
    }

    /**
     * @param $query
     * @param int $month
     *
     * @return mixed
     */
    public function scopeMonth( $query, int $month )
    {
        // where month == $month OR if schedule is not monthly/annual/adhoc then where month is null
        return $query->where(function ( $query ) use ( $month )
        {
            $query->where('month_of_year', $month)
                  ->orWhere(function ( $query ) use ( $month )
                  {
                      $query->whereNull('month_of_year')
                            ->where(function ( $query )
                            {
                                $query->where('is_annually', 0)
                                      ->where('is_adhoc', 0);
                            });
                  });
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
        // where month is between $from and $to OR if schedule is not monthly/annual/adhoc then where month is null
        return $query->where(function ( $query ) use ( $from, $to )
        {
            $query->whereBetween('month_of_year', compact('from', 'to'))
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('month_of_year')
                            ->where(function ( $query )
                            {
                                $query->where('is_annually', 0)
                                      ->where('is_adhoc', 0);
                            });
                  });
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
        // where year == $year OR if schedule is not annual/adhoc then where day of month is null
        return $query->where(function ( $query ) use ( $year )
        {
            $query->where('year', $year)
                  ->orWhere(function ( $query ) use ( $year )
                  {
                      $query->whereNull('year')
                            ->where('is_adhoc', 0);
                  });
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
        return $query->where(function ( $query ) use ( $from, $to )
        {
            $query->whereBetween('year', compact('from', 'to'))
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('year')
                            ->where('is_adhoc', 0);
                  });
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
        // where hour == $hour OR if schedule is not annual/adhoc/quarterly/monthly/weekly then where day of month is null
        return $query->where(function ( $query ) use ( $hour )
        {
            $query->where('hour', $hour)
                  ->orWhere(function ( $query ) use ( $hour )
                  {
                      $query->whereNull('hour')
                            ->where('is_adhoc', 0)
                            ->where('is_daily', 0)
                            ->where('is_weekly', 0)
                            ->where('is_monthly', 0)
                            ->where('is_annually', 0)
                            ->where('is_quarterly', 0);

                  });
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
        return $query->where(function ( $query ) use ( $from, $to )
        {
            $query->whereBetween('hour', compact('from', 'to'))
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('hour')
                            ->where('is_adhoc', 0)
                            ->where('is_daily', 0)
                            ->where('is_weekly', 0)
                            ->where('is_monthly', 0)
                            ->where('is_annually', 0)
                            ->where('is_quarterly', 0);
                  });
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
        // where minute == $minute OR if schedule is not annual/adhoc/quarterly/monthly/weekly then where day of month is null
        return $query->where(function ( $query ) use ( $minute )
        {
            $query->where('minute', $minute)
                  ->orWhere(function ( $query ) use ( $minute )
                  {
                      $query->whereNull('minute')
                            ->where('is_adhoc', 0)
                            ->where('is_daily', 0)
                            ->where('is_weekly', 0)
                            ->where('is_monthly', 0)
                            ->where('is_annually', 0)
                            ->where('is_quarterly', 0)
                            ->where('is_hourly', 0);
                  });
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
        return $query->where(function ( $query ) use ( $from, $to )
        {
            $query->whereBetween('minute', compact('from', 'to'))
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('minute')
                            ->where('is_adhoc', 0)
                            ->where('is_daily', 0)
                            ->where('is_weekly', 0)
                            ->where('is_monthly', 0)
                            ->where('is_annually', 0)
                            ->where('is_quarterly', 0)
                            ->where('is_hourly', 0);
                  });
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
        return $query->where('starts_at', '<=', $dt->toDateTimeString())
                     ->where('expires_at', '>=', $dt->toDateTimeString());
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
        return $query->where('starts_at', '<=', $dtFrom->toDateTimeString())
                     ->where('expires_at', '>=', $dtTo->toDateTimeString());
    }

    /**
     * @param $query
     * @param Carbon|null $dt
     *
     * @return mixed
     */
    public function scopeIsExpired( $query, Carbon $dt = null )
    {
        return $query->where('expires_at', '<=', $dt->toDateTimeString());
    }

}