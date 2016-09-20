<?php

use Wtbi\Schedulable\Models\Schedule;

require_once 'DbTestCase.php';
require_once 'Models/Plan.php';

/**
 * Class ModelScopeTest
 */
class ModelScopeTest extends DbTestCase
{
    /**
     * Setup DB before each test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate(__DIR__ . '/migrations');
    }

    /**
     *
     */
    public function test_i_can_get_all_scheduled_objects_due_on_a_date()
    {
        $dt = \Carbon\Carbon::now();
        $this->createMinutelyPlan();
        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $dt = \Carbon\Carbon::now()
                            ->addMinutes(5);

        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $this->assertEquals(7, Plan::dueOn($dt)
                                   ->count());
    }

    public function test_i_can_get_all_the_minutely_plans()
    {
        $dt = \Carbon\Carbon::now();
        $this->createMinutelyPlan();
        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $this->assertEquals(1, Plan::isMinutely()
                                   ->count());
    }

    public function test_i_can_get_all_the_hourly_plans()
    {
        $dt = \Carbon\Carbon::now();
        $this->createMinutelyPlan();
        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $this->assertEquals(1, Plan::isHourly()
                                   ->count());
    }

    public function test_i_can_get_all_the_daily_plans()
    {
        $dt = \Carbon\Carbon::now();
        $this->createMinutelyPlan();
        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $this->assertEquals(1, Plan::isDaily()
                                   ->count());
    }

    public function test_i_can_get_all_the_weekly_plans()
    {
        $dt = \Carbon\Carbon::now();
        $this->createMinutelyPlan();
        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $this->assertEquals(1, Plan::isWeekly()
                                   ->count());
    }

    public function test_i_can_get_all_the_monthly_plans()
    {
        $dt = \Carbon\Carbon::now();
        $this->createMinutelyPlan();
        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $this->assertEquals(1, Plan::isMonthly()
                                   ->count());
    }

    public function test_i_can_get_all_the_annually_plans()
    {
        $dt = \Carbon\Carbon::now();
        $this->createMinutelyPlan();
        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $this->assertEquals(1, Plan::isAnnually()
                                   ->count());
    }

    public function test_i_can_get_all_the_adhoc_plans()
    {
        $dt = \Carbon\Carbon::now();
        $this->createMinutelyPlan();
        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $this->assertEquals(1, Plan::isAdhoc()
                                   ->count());
    }

    public function test_i_can_get_all_active_plans()
    {
        $this->createMonthlyPlan()
             ->expiresAt(\Carbon\Carbon::now()
                                       ->addMonth()
                                       ->toDateTimeString())
             ->save();

        $this->createMonthlyPlan()
             ->expiresAt(\Carbon\Carbon::now()
                                       ->subYear()
                                       ->toDateTimeString())
             ->save();

        $this->assertEquals(1, Plan::isActive()
                                   ->count());
    }

    public function test_i_can_get_all_the_expired_plans()
    {
        $this->createMonthlyPlan()
             ->expiresAt(\Carbon\Carbon::now()
                                       ->addMonth()
                                       ->toDateTimeString())
             ->save();

        $this->createMonthlyPlan()
             ->expiresAt(\Carbon\Carbon::now()
                                       ->subYear()
                                       ->toDateTimeString())
             ->save();

        $this->assertEquals(1, Plan::isExpired()
                                   ->count());
    }

    public function test_i_can_get_all_plans_that_will_run_at_least_once_between_two_dates()
    {
        $dt = \Carbon\Carbon::now();
        $this->createMinutelyPlan();
        $this->createHourlyPlan($dt);
        $this->createDailyPlan($dt);
        $this->createWeeklyPlan($dt);
        $this->createMonthlyPlan($dt);
        $this->createAnnualPlan($dt);
        $this->createAdhocPlan($dt);

        $this->assertEquals(4, Plan::between($dt, \Carbon\Carbon::now()
                                                                ->addWeek())
                                   ->count());
    }

    public function test_i_can_get_an_array_of_dates_for_each_active_schedule()
    {
        $dt = \Carbon\Carbon::now()
                            ->second(0);
        $minutely = $this->createMinutelyPlan()
                         ->getObject();
        $hourly = $this->createHourlyPlan($dt)
                       ->getObject();
        $daily = $this->createDailyPlan($dt)
                      ->getObject();
        $weekly = $this->createWeeklyPlan($dt)
                       ->getObject();
        $monthly = $this->createMonthlyPlan($dt)
                        ->getObject();
        $annual = $this->createAnnualPlan($dt)
                       ->getObject();
        $adhoc = $this->createAdhocPlan($dt)
                      ->getObject();

        $collection = Schedule::allRunDatesBetween($dt, \Carbon\Carbon::now()
                                                                      ->addWeek()
                                                                      ->second(0));

        $this->assertTrue($collection instanceof \Illuminate\Support\Collection);

        $this->assertEquals(( 7 * 24 * 60 ), $collection[ $minutely->id ]->events->count());
        $this->assertEquals(( 7 * 24 ), $collection[ $hourly->id ]->events->count());
        $this->assertEquals(7, $collection[ $daily->id ]->events->count());
        $this->assertEquals(1, $collection[ $weekly->id ]->events->count());
        $this->assertFalse(isset( $collection[ $monthly->id ] ));
        $this->assertFalse(isset( $collection[ $annual->id ] ));
        $this->assertFalse(isset( $collection[ $adhoc->id ] ));
    }

    public function test_i_can_get_an_array_of_dates_for_a_schedule()
    {
        $plan = $this->createMonthlyPlan()
                     ->getObject();

        $this->assertEquals(12, $plan->runDatesBetween(\Carbon\Carbon::now(), \Carbon\Carbon::now()
                                                                                            ->addYear())
                                     ->first()->events->count());
    }
}