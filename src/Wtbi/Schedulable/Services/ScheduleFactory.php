<?php

namespace Wtbi\Schedulable\Services;

/**
 * Class Builder
 * @package Wtbi\Schedulable
 */
use Cron\CronExpression;
use ReflectionObject;
use ReflectionProperty;
use Wtbi\Schedulable\Contracts\IsSchedulable;
use Wtbi\Schedulable\Contracts\Schedule;

/**
 * Class Builder
 * @package Wtbi\Schedulable
 */
class ScheduleFactory implements \Wtbi\Schedulable\Contracts\ScheduleFactory
{
    /**
     * @var array
     */
    private $mappings = [
        '@yearly'   => '0 0 1 1 *',
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
    public function __call( $name, $arguments = [ ] )
    {
        $this->checkPropertyExists($name);

        if ( sizeof($arguments) > 0 )
        {
            if ( $this->isFlag($name) )
                $this->resetFlags();

            $this->{snake_case($name)} = head($arguments);

            return $this;
        }

        return $this->{snake_case($name)};
    }

    /**
     * @param null $object
     *
     * @return static
     */
    public function setObject( $object )
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return static
     */
    public function save()
    {
        return $this->object->hasSchedule() ? $this->update() : $this->create();
    }

    /**
     * @return $this
     */
    public function load()
    {
        $scheduleVars = $this->object->schedule()
                                     ->toArray();

        foreach ( $this->schedule->fillableFromArray($scheduleVars) as $key => $value )
            $this->$key = $value;

        $this->schedule = $this->object->schedule;

        return $this;
    }

    /**
     * @return mixed
     */
    public function refresh()
    {
        return new $this($this->object);
    }

    /**
     * @param string $expression
     *
     * @return static
     */
    public function loadFromCron( string $expression )
    {
        $cron = CronExpression::factory($expression);

        foreach ( $this->cronKeys as $key => $value )
            $this->$value = ( $part = $cron->getExpression($key) ) == '*' ? null : $part;

        if ( in_array($expression, array_keys($this->mappings)) )
            $this->resetFlags()->{'is_' . substr($expression, 1)} = true;

        if ( in_array($expression, $this->mappings) )
            $this->resetFlags()->{'is_' . array_flip($this->mappings)[ $expression ]} = true;

        return $this;
    }

    /**
     * @return static
     */
    protected function update()
    {
        $this->object->schedule()
                     ->update($this->getScheduleProperties());

        return $this;
    }

    /**
     * @return static
     */
    protected function create()
    {
        $this->object->schedule()
                     ->create($this->getScheduleProperties());

        return $this;
    }

    /**
     * @return array
     */
    protected function getScheduleProperties()
    {
        return $this->schedule->fillableFromArray(get_object_vars($this));
    }

    /**
     * @param $name
     *
     * @return static
     * @throws \Exception
     */
    protected function checkPropertyExists( $name )
    {
        if ( !property_exists($this, snake_case($name)) || !$this->isProtected($name) )
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

        return in_array(snake_case($name), $reflect->getProperties(ReflectionProperty::IS_PROTECTED));
    }

    /**
     *
     */
    protected function resetFlags()
    {
        foreach ( $this->flags as $flag )
            $this->$flag = false;
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
}