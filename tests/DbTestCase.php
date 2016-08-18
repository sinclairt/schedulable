<?php

use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;

require_once __DIR__ . '/../src/Wtbi/Schedulable/Providers/SchedulableServiceProvider.php';

abstract class DbTestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $app->register(\Wtbi\Schedulable\Providers\SchedulableServiceProvider::class);

        $app->make('Illuminate\Contracts\Console\Kernel')
            ->bootstrap();

        return $app;
    }

    /**
     * Setup DB before each test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->app[ 'config' ]->set('database.default', 'sqlite');
        $this->app[ 'config' ]->set('database.connections.sqlite.database', ':memory:');

        $this->migrate();
    }

    /**
     * run package database migrations
     *
     * @param string $path
     */
    public function migrate( $path = __DIR__ . "/../src/migrations" )
    {
        $fileSystem = new Filesystem;
        $classFinder = new ClassFinder;

        foreach ( $fileSystem->files($path) as $file )
        {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);

            ( new $migrationClass )->up();
        }
    }

    /**
     * @param array $attributes
     *
     * @return Plan
     */
    protected function makePlanWithSchedule( $attributes )
    {
        $plan = $this->makePlanWithoutSchedule();

        $plan->schedule()
             ->create($attributes);

        return $plan;
    }

    /**
     * @return Plan
     */
    protected function makePlanWithoutSchedule()
    {
        $plan = new Plan([ 'name' => 'test_plan' ]);

        $plan->save();

        return $plan;
    }

    /**
     * @return \Wtbi\Schedulable\Services\ScheduleFactory
     */
    protected function createMinutelyPlan()
    {
        return schedule(Plan::create([ 'name' => 'minutely' ]))
            ->minutely()
            ->save();
    }

    /**
     * @param $dt
     *
     * @return \Wtbi\Schedulable\Services\ScheduleFactory
     */
    protected function createHourlyPlan( \Carbon\Carbon $dt = null )
    {
        if ( is_null($dt) )
            $dt = \Carbon\Carbon::now();

        return schedule(Plan::create([ 'name' => 'hourly' ]))
            ->hourly()
            ->minute($dt->minute)
            ->save();
    }

    /**
     * @param $dt
     *
     * @return \Wtbi\Schedulable\Services\ScheduleFactory
     */
    protected function createDailyPlan( \Carbon\Carbon $dt = null )
    {
        if ( is_null($dt) )
            $dt = \Carbon\Carbon::now();

        return schedule(Plan::create([ 'name' => 'daily' ]))
            ->daily()
            ->minute($dt->minute)
            ->hour($dt->hour)
            ->save();
    }

    /**
     * @param $dt
     *
     * @return \Wtbi\Schedulable\Services\ScheduleFactory
     */
    protected function createWeeklyPlan( \Carbon\Carbon $dt = null )
    {
        if ( is_null($dt) )
            $dt = \Carbon\Carbon::now();

        return schedule(Plan::create([ 'name' => 'weekly' ]))
            ->weekly()
            ->minute($dt->minute)
            ->hour($dt->hour)
            ->dayOfWeek($dt->dayOfWeek)
            ->save();
    }

    /**
     * @param $dt
     *
     * @return \Wtbi\Schedulable\Services\ScheduleFactory
     */
    protected function createMonthlyPlan( \Carbon\Carbon $dt = null )
    {
        if ( is_null($dt) )
            $dt = \Carbon\Carbon::now();

        return schedule(Plan::create([ 'name' => 'monthly' ]))
            ->monthly()
            ->minute($dt->minute)
            ->hour($dt->hour)
            ->dayOfMonth($dt->day)
            ->save();
    }

    /**
     * @param $dt
     *
     * @return \Wtbi\Schedulable\Services\ScheduleFactory
     */
    protected function createAnnualPlan( \Carbon\Carbon $dt = null )
    {
        if ( is_null($dt) )
            $dt = \Carbon\Carbon::now();

        return schedule(Plan::create([ 'name' => 'annual' ]))
            ->annually()
            ->minute($dt->minute)
            ->hour($dt->hour)
            ->dayOfMonth($dt->day)
            ->monthOfYear($dt->month)
            ->save();
    }

    /**
     * @param $dt
     *
     * @return \Wtbi\Schedulable\Services\ScheduleFactory
     */
    protected function createAdhocPlan( \Carbon\Carbon $dt = null )
    {
        if ( is_null($dt) )
            $dt = \Carbon\Carbon::now();

        return schedule(Plan::create([ 'name' => 'adhoc' ]))
            ->adhoc()
            ->minute($dt->minute)
            ->hour($dt->hour)
            ->dayOfMonth($dt->day)
            ->monthOfYear($dt->month)
            ->year($dt->year)
            ->save();
    }
}