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
 * @property Carbon $next_runs_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
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
 * @method static isExpired( $query, Carbon $dt = null )
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
        'next_runs_at'
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
        'next_runs_at'
    ];

    /**
     * @param Carbon $dtTo
     * @param $active
     * @param $dtFrom
     * @param $schedule
     *
     * @return array
     */
    protected function getDatesForSchedule( $schedule, Carbon $dtFrom, Carbon $dtTo = null, $active = true )
    {
        $events = [ ];

        while ( $dtFrom->timestamp <= $dtTo->timestamp )
        {
            if ( ( ( $dtFrom->timestamp >= $schedule->starts_at || is_null($schedule->starts_at) ) && ( $dtFrom->timestamp <= $schedule->expires_at || is_null($schedule->expires_at) ) ) || !$active )
                $events[] = [ 'schedule_id' => $schedule->id, 'occurs_at' => $schedule->next($dtFrom)
                                                                                      ->toDateTimeString() ];

            switch (self::getScheduleType($schedule))
            {
                case 'minutely':
                    $dtFrom->addMinute();
                    break;
                case 'hourly':
                    $dtFrom->addHour();
                    break;
                case 'daily':
                    $dtFrom->addDay();
                    break;
                case 'weekly':
                    $dtFrom->addWeek();
                    break;
                case 'monthly':
                    $dtFrom->addMonth();
                    break;
                case 'annually':
                    $dtFrom->addYear();
                    break;
                case 'adhoc':
                    break 2;
            }
        }

        return $events;
    }

    /**
     * @param Carbon $dtFrom
     * @param Carbon $dtTo
     * @param $events
     *
     * @return Collection
     */
    protected function collectEvents( $events, Carbon $dtFrom, Carbon $dtTo = null )
    {
        return collect($events)
            ->where('occurs_at', '>=', $dtFrom->toDateTimeString())
            ->where('occurs_at', '<=', $dtTo->toDateTimeString())
            ->groupBy('schedule_id')
            ->map(function ( $item, $key )
            {
                $schedule = Schedule::find($key);

                $schedule->events = $item->map(function ( $item )
                {
                    return Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $item[ 'occurs_at' ]);
                });

                return $schedule;
            });
    }

    /**
     * @param $schedule
     *
     * @return mixed
     */
    public function getScheduleType( $schedule )
    {
        return head(array_keys(array_filter([
            'minutely' => $schedule->is_minutely == 1,
            'hourly'   => $schedule->is_hourly == 1,
            'daily'    => $schedule->is_daily == 1,
            'weekly'   => $schedule->is_weekly == 1,
            'monthly'  => $schedule->is_monthly == 1,
            'annually' => $schedule->is_annually == 1,
            'adhoc'    => $schedule->is_adhoc == 1,
        ])));
    }

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
        if ( is_null($dt) )
            $dt = Carbon::now();

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
        if ( is_null($dt) )
            $dt = Carbon::now();

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
     * @param Carbon $dtFrom
     * @param Carbon|null $dtTo
     * @param bool $active
     *
     * @return Collection
     */
    public static function allRunDatesBetween( Carbon $dtFrom, Carbon $dtTo = null, $active = true )
    {
        if ( is_null($dtTo) )
            $dtTo = Carbon::now();

        $schedules = $active ? Schedule::isActiveBetween($dtFrom, $dtTo)
                                       ->get() : Schedule::all();

        $instance = new static;

        $events = [ ];
        foreach ( $schedules as $schedule )
        {
            $from = clone $dtFrom;

            $events = array_merge($instance->getDatesForSchedule($schedule, $from, $dtTo, $active), $events);
        }

        return $instance->collectEvents($events, $dtFrom, $dtTo);
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
        if ( is_null($dtTo) )
            $dtTo = Carbon::now();

        $from = clone $dtFrom;

        return $this->collectEvents($this->getDatesForSchedule($this, $dtFrom, $dtTo, $active), $from, $dtTo);
    }

    /**
     * @param Carbon|null $dt
     *
     * @return bool
     */
    public function isDue( Carbon $dt = null )
    {
        if ( is_null($dt) )
            $dt = Carbon::now();

        return CronExpression::factory($this->getCronExpression())
                             ->isDue($dt);
    }

    /**
     * @return string
     */
    protected function getCronExpression()
    {
        return implode(' ', [
            0 => is_null($this->minute) ? '*' : $this->minute,
            1 => is_null($this->hour) ? '*' : $this->hour,
            2 => is_null($this->day_of_month) ? '*' : $this->day_of_month,
            3 => is_null($this->month_of_year) ? '*' : $this->month_of_year,
            4 => is_null($this->day_of_week) ? '*' : $this->day_of_week,
        ]);
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
        $query = $query->minute($dt->minute)
                       ->hour($dt->hour)
                       ->day($dt)
                       ->month($dt->month)
                       ->year($dt->year);

        return $active ? $query->isActive($dt) : $query;
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
        return $query->where('is_annually', 1);
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
     *
     * @return mixed
     */
    public function scopeIsNotMinutely( $query )
    {
        return $query->where('is_minutely', 0);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsNotHourly( $query )
    {
        return $query->where('is_hourly', 0);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsNotDaily( $query )
    {
        return $query->where('is_daily', 0);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsNotWeekly( $query )
    {
        return $query->where('is_weekly', 0);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsNotMonthly( $query )
    {
        return $query->where('is_monthly', 0);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsNotAnnually( $query )
    {
        return $query->where('is_annual', 0);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsNotQuarterly( $query )
    {
        return $query->where('is_quarterly', 0);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeIsNotAdhoc( $query )
    {
        return $query->where('is_adhoc', 0);
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
        if ( is_null($dtTo) )
            $dtTo = Carbon::now();

        if ( $active )
            $query = $query->isActiveBetween($dtFrom, $dtTo);

        $schedules = $active ? Schedule::isActiveBetween($dtFrom, $dtTo)
                                       ->get() : Schedule::all();

        $events = [ ];
        foreach ( $schedules as $schedule )
        {
            $from = clone $dtFrom;

            $events = array_merge($this->getDatesForSchedule($schedule, $from, $dtTo, $active), $events);
        }

        $schedule_ids = collect($events)
            ->where('occurs_at', '>=', $dtFrom->toDateTimeString())
            ->where('occurs_at', '<=', $dtTo->toDateTimeString())
            ->pluck('schedule_id')
            ->toArray();

        return $query->whereIn('schedules.id', $schedule_ids);
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
        if ( is_null($dtTo) )
            $dtTo = Carbon::now();

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
            $query->where(function ( $query ) use ( $day )
            {
                $query->where('day_of_month', $day)
                      ->where(function ( $query )
                      {
                          $query->isMonthly()
                                ->orWhere('is_annually', 1)
                                ->orWhere('is_adhoc', 1)
                                ->orWhere('is_quarterly', 1);
                      });
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('day_of_month')
                            ->where(function ( $query )
                            {
                                $query->isMinutely()
                                      ->orWhere('is_hourly', 1)
                                      ->orWhere('is_daily', 1)
                                      ->orWhere('is_weekly', 1);
                            });
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
        if ( is_null($dtTo) )
            $dtTo = Carbon::now();

        // where day of month is between $from and $to OR if schedule is not monthly or annual then where day of month is null
        return $query->where(function ( $query ) use ( $dtFrom, $dtTo )
        {
            $query->where(function ( $query ) use ( $dtFrom, $dtTo )
            {
                $query->whereBetween('day_of_month', [ $dtFrom->day, $dtTo->day ])
                      ->where(function ( $query )
                      {
                          $query->isMonthly()
                                ->orWhere('is_annually', 1)
                                ->orWhere('is_adhoc', 1)
                                ->orWhere('is_quarterly', 1);
                      });
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('day_of_month')
                            ->where(function ( $query )
                            {
                                $query->isMinutely()
                                      ->orWhere('is_hourly', 1)
                                      ->orWhere('is_daily', 1)
                                      ->orWhere('is_weekly', 1);
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
                            ->isNotWeekly();
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
        if ( is_null($dtTo) )
            $dtTo = Carbon::now();

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
                            ->isNotWeekly();
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
                    $query->isLastDayOfMonth()
                          ->where(function ( $query )
                          {
                              $query->isMonthly()
                                    ->orWhere('is_annually', 1)
                                    ->orWhere('is_adhoc', 1)
                                    ->orWhere('is_quarterly', 1);
                          });
                })
                      ->orWhere(function ( $query )
                      {
                          $query->whereNull('is_last_day_of_month')
                                ->where(function ( $query )
                                {
                                    $query->isMinutely()
                                          ->orWhere('is_hourly', 1)
                                          ->orWhere('is_daily', 1)
                                          ->orWhere('is_weekly', 1);
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
            $query->where(function ( $query ) use ( $month )
            {
                $query->where('month_of_year', $month)
                      ->where(function ( $query )
                      {
                          $query->isAdhoc()
                                ->orWhere('is_annually', 1);
                      });
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('month_of_year')
                            ->where(function ( $query )
                            {
                                $query->isNotAdhoc()
                                      ->orWhere('is_annually', 0);
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
            $query->where(function ( $query ) use ( $from, $to )
            {
                $query->whereBetween('month_of_year', compact('from', 'to'))
                      ->where(function ( $query )
                      {
                          $query->isAdhoc()
                                ->orWhere('is_annually', 1);
                      });
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('month_of_year')
                            ->where(function ( $query )
                            {
                                $query->isNotAdhoc()
                                      ->orWhere('is_annually', 0);
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
            $query->where(function ( $query ) use ( $year )
            {
                $query->where('year', $year)
                      ->isAdhoc();
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('year')
                            ->isNotAdhoc();
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
            $query->where(function ( $query ) use ( $from, $to )
            {
                $query->whereBetween('year', compact('from', 'to'))
                      ->isAdhoc();
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('hour')
                            ->isNotAdhoc();
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
            $query->where(function ( $query ) use ( $hour )
            {
                $query->where('hour', $hour)
                      ->where(function ( $query )
                      {
                          $query->isNotHourly()
                                ->orWhere('is_minutely', 0);
                      });
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('hour')
                            ->where(function ( $query )
                            {
                                $query->isHourly()
                                      ->orWhere('is_minutely', 1);
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
    public function scopeHourBetween( $query, int $from, int $to )
    {
        return $query->where(function ( $query ) use ( $from, $to )
        {
            $query->where(function ( $query ) use ( $from, $to )
            {
                $query->whereBetween('hour', compact('from', 'to'))
                      ->where(function ( $query )
                      {
                          $query->isNotHourly()
                                ->orWhere('is_minutely', 0);
                      });
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('hour')
                            ->where(function ( $query )
                            {
                                $query->isHourly()
                                      ->orWhere('is_minutely', 1);
                            });
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
            $query->where(function ( $query ) use ( $minute )
            {
                $query->where('minute', $minute)
                      ->isNotMinutely();
            })
                  ->orWhere(function ( $query ) use ( $minute )
                  {
                      $query->whereNull('minute')
                            ->isMinutely();
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
            $query->where(function ( $query ) use ( $from, $to )
            {
                $query->whereBetween('minute', compact('from', 'to'))
                      ->isNotMinutely();
            })
                  ->orWhere(function ( $query )
                  {
                      $query->whereNull('minute')
                            ->isMinutely();
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
        if ( is_null($dt) )
            $dt = Carbon::now();

        return $query->where(function ( $query ) use ( $dt )
        {
            $query->where('starts_at', '<=', $dt->toDateTimeString())
                  ->orWhereNull('starts_at');
        })
                     ->where(function ( $query ) use ( $dt )
                     {
                         $query->where('expires_at', '>=', $dt->toDateTimeString())
                               ->orWhereNull('expires_at');
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
        if ( is_null($dtTo) )
            $dtTo = Carbon::now();

        return $query->where(function ( $query ) use ( $dtFrom )
        {
            $query->where('starts_at', '<=', $dtFrom->toDateTimeString())
                  ->orWhereNull('starts_at');

        })
                     ->where(function ( $query ) use ( $dtTo )
                     {
                         $query->where('expires_at', '>=', $dtTo->toDateTimeString())
                               ->orWhereNull('expires_at');
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
        if ( is_null($dt) )
            $dt = Carbon::now();

        return $query->where('expires_at', '<=', $dt->toDateTimeString());
    }

}