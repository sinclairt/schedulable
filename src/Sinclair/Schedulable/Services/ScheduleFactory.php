<?php

namespace Sinclair\Schedulable\Services;

use Cron\CronExpression;
use ReflectionObject;
use ReflectionProperty;
use Sinclair\Schedulable\Contracts\IsSchedulable;
use Sinclair\Schedulable\Contracts\Schedule;

/**
 * Class ScheduleFactory
 * @package Sinclair\Schedulable
 *
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory minutely()
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory hourly()
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory daily()
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory weekly()
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory monthly()
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory annually()
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory quarterly()
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory adhoc()
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|bool isMinutely( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|bool isHourly( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|bool isDaily( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|bool isWeekly( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|bool isMonthly( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|bool isAnnually( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|bool isQuarterly( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|bool isAdhoc( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|integer|null minute( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|integer|null hour( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|integer|null dayOfWeek( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|integer|null dayOfMonth( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|integer|null monthOfYear( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|integer|null year( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|boolean isLastDayOfMonth( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|integer frequencyN( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|\Carbon\Carbon|null startsAt( $value = null )
 * @method static \Sinclair\Schedulable\Services\ScheduleFactory|\Carbon\Carbon|null expiresAt( $value = null )
 */
class ScheduleFactory implements \Sinclair\Schedulable\Contracts\ScheduleFactory
{
    /**
     * @var array
     */
    private $mappings = [
        '@annually' => '0 0 1 1 *',
        '@monthly'  => '0 0 1 * *',
        '@weekly'   => '0 0 * * 0',
        '@daily'    => '0 0 * * *',
        '@hourly'   => '0 * * * *'
    ];

    /**
     * @var array
     */
    private $cronKeys = [
        'minute',
        'hour',
        'day_of_month',
        'month_of_year',
        'day_of_week',
        'year',
    ];

    private $requiredFields = [
        'is_minutely' => [],
        'is_hourly'   => [ 'minute' ],
        'is_daily'    => [ 'hour', 'minute' ],
        'is_weekly'   => [ 'day_of_week', 'hour', 'minute' ],
        'is_monthly'  => [ 'day_of_month', 'hour', 'minute' ],
        'is_annually' => [ 'month_of_year', 'day_of_month', 'hour', 'minute' ],
        'is_adhoc'    => [ 'year', 'month_of_year', 'day_of_month', 'hour', 'minute' ]
    ];

    /**
     * @var null
     */
    private $object;

    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * @var array
     */
    private $flags = [
        'is_adhoc',
        'is_minutely',
        'is_hourly',
        'is_daily',
        'is_weekly',
        'is_monthly',
        'is_annually',
    ];

    /**
     * @var null
     */
    protected $minute = null;

    /**
     * @var null
     */
    protected $hour = null;

    /**
     * @var null
     */
    protected $day_of_week = null;

    /**
     * @var null
     */
    protected $day_of_month = null;

    /**
     * @var null
     */
    protected $month_of_year = null;

    /**
     * @var null
     */
    protected $year = null;

    /**
     * @var bool
     */
    protected $is_last_day_of_month = false;

    /**
     * @var bool
     */
    protected $is_adhoc = false;

    /**
     * @var bool
     */
    protected $is_minutely = false;

    /**
     * @var bool
     */
    protected $is_hourly = false;

    /**
     * @var bool
     */
    protected $is_daily = false;

    /**
     * @var bool
     */
    protected $is_weekly = false;

    /**
     * @var bool
     */
    protected $is_monthly = false;

    /**
     * @var bool
     */
    protected $is_annually = false;

    /**
     * @var bool
     */
    protected $is_quarterly = false;

    /**
     * @var int
     */
    protected $frequency_n = 0;

    /**
     * @var null
     */
    protected $starts_at = null;

    /**
     * @var null
     */
    protected $expires_at = null;

    /**
     * Builder constructor.
     *
     * @param IsSchedulable $object
     * @param Schedule $schedule
     */
    public function __construct( IsSchedulable $object = null, Schedule $schedule = null )
    {
        $this->object = $object;

        $this->schedule = $schedule;

        if ( !is_null($this->object) )
            if ( $this->object->hasSchedule() )
                $this->load();
    }

    /**
     * @param $name
     * @param array $arguments
     *
     * @return $this|mixed
     * @throws \Exception
     */
    public function __call( $name, $arguments = [] )
    {
        $this->checkPropertyExists($name);

        // are we setting a property?
        if ( sizeof($arguments) > 0 )
        {
            $this->{snake_case($name)} = head($arguments);

            return $this;
        }

        // are we setting a flag?
        if ( $this->isFlag('is_' . $name) )
        {
            $this->resetFlags();

            $this->{'is_' . snake_case($name)} = true;

            return $this;
        }

        // or are we trying to get a property?
        return $this->{snake_case($name)};
    }

    /**
     * @param Schedule $schedule
     *
     * @return ScheduleFactory
     */
    public function setSchedule( Schedule $schedule ): ScheduleFactory
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * @return Schedule
     */
    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    /**
     * @param null $object
     *
     * @return ScheduleFactory
     */
    public function setObject( $object )
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return null|IsSchedulable
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return ScheduleFactory
     * @throws \Exception
     */
    public function save()
    {
        $this->validateRequiredFieldsForSchedule();

        return $this->object->hasSchedule() ? $this->update() : $this->create();
    }

    /**
     * @return ScheduleFactory
     */
    public function load()
    {
        return $this->reloadProperties($this->object->schedule->toArray());
    }

    /**
     * @param null $schedule
     *
     * @return ScheduleFactory
     */
    public function loadFromSchedule( $schedule = null )
    {
        if ( !is_null($schedule) )
            $this->setSchedule($schedule);

        return $this->reloadProperties($this->schedule->toArray());
    }

    /**
     * @return mixed
     */
    public function refresh()
    {
        return new $this($this->object);
    }

    /**
     * @return ScheduleFactory
     */
    public function resetSchedule()
    {
        $this->minute = null;
        $this->hour = null;
        $this->day_of_week = null;
        $this->day_of_month = null;
        $this->month_of_year = null;
        $this->year = null;
        $this->is_last_day_of_month = false;
        $this->is_adhoc = false;
        $this->is_minutely = false;
        $this->is_hourly = false;
        $this->is_daily = false;
        $this->is_weekly = false;
        $this->is_monthly = false;
        $this->is_annually = false;
        $this->is_quarterly = false;
        $this->frequency_n = 0;
        $this->starts_at = null;
        $this->expires_at = null;

        return $this;
    }

    /**
     * @param string $expression
     *
     * @return ScheduleFactory
     */
    public function loadFromCron( string $expression )
    {
        $cron = CronExpression::factory($expression);

        foreach ( $this->cronKeys as $key => $value )
            $this->$value = ( $part = $cron->getExpression($key) ) == '*' ? null : $part;

        if ( in_array($expression, array_keys($this->mappings)) )
            $this->resetFlags()->{'is_' . snake_case(substr($expression, 1))} = true;

        if ( in_array($expression, $this->mappings) )
            $this->resetFlags()->{'is_' . snake_case(substr(array_flip($this->mappings)[ $expression ], 1))} = true;

        return $this;
    }

    /**
     * @return ScheduleFactory
     */
    protected function update()
    {
        $this->object->schedule()
                     ->update($this->getScheduleProperties());

        $this->object = $this->object->fresh();

        $this->schedule = $this->object->schedule->fresh();

        $this->setScheduleNextRunDate();

        return $this;
    }

    /**
     * @return ScheduleFactory
     */
    protected function create()
    {
        $this->object->schedule()
                     ->create($this->getScheduleProperties());

        $this->object = $this->object->fresh();

        $this->schedule = $this->object->schedule;

        $this->setScheduleNextRunDate();

        return $this;
    }

    /**
     * @return array
     */
    protected function getScheduleProperties()
    {
        return array_filter(get_object_vars($this), function ( $value, $key )
        {
            return $this->schedule->isFillable($key);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param $name
     *
     * @return ScheduleFactory
     * @throws \Exception
     */
    protected function checkPropertyExists( $name )
    {
        if ( ( !property_exists($this, snake_case($name)) || !$this->isProtected($name) ) && ( !property_exists($this, 'is_' . snake_case($name)) || !$this->isProtected('is_' . $name) ) )
            throw new \Exception(snake_case($name) . ' is not a property');

        return $this;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    protected function isProtected( $name )
    {
        $reflect = new ReflectionObject($this);

        return collect($reflect->getProperties(ReflectionProperty::IS_PROTECTED))
                   ->where('name', snake_case($name))
                   ->count() > 0;
    }

    /**
     * @return ScheduleFactory
     */
    protected function resetFlags()
    {
        foreach ( $this->flags as $flag )
            $this->$flag = false;

        return $this;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    protected function isFlag( $name )
    {
        return in_array(snake_case($name), $this->flags);
    }

    /**
     * @param $scheduleVars
     *
     * @return ScheduleFactory
     */
    protected function reloadProperties( $scheduleVars )
    {
        foreach ( $scheduleVars as $key => $value )
            if ( $this->schedule->isFillable($key) && $this->isProtected($key) )
                $this->$key = $value;

        $this->schedule = $this->object->schedule;

        return $this;
    }

    protected function setScheduleNextRunDate()
    {
        $this->schedule->next_runs_at = $this->schedule->next()
                                                       ->toDateTimeString();

        $this->schedule->save();

        $this->schedule = $this->schedule->fresh();
    }

    protected function validateRequiredFieldsForSchedule()
    {
        $flags = [];
        foreach ( $this->flags as $flag )
            $flags[ $flag ] = $this->$flag;

        $key = head(array_keys(array_filter($flags)));

        $required = $this->requiredFields[ $key ];

        $fields = [];

        foreach ( $required as $field )
            $fields[ $field ] = $this->$field;

        // zeroes will be ok here
        $fields = array_filter($fields, function ( $value )
        {
            return null !== $value;
        });

        if ( sizeof($missing = array_diff($required, array_keys($fields))) > 0 )
            throw new \Exception('The following fields need to be set: ' . implode(', ', $missing));

        // lets remove any unwanted fields
        $this->minute = null;
        $this->hour = null;
        $this->day_of_week = null;
        $this->day_of_month = null;
        $this->month_of_year = null;
        $this->year = null;

        foreach ( $fields as $field => $value )
            $this->$field = $value;

        $this->$key = true;
    }
}