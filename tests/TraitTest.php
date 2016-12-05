<?php

require_once 'DbTestCase.php';
require_once 'Models/Plan.php';

class TraitTest extends DbTestCase
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

    public function test_i_update_the_current_plans_schedule_when_it_already_has_one()
    {
        $this->createMonthlyPlan()
             ->expiresAt(\Carbon\Carbon::now()
                                       ->addMonth()
                                       ->toDateTimeString())
             ->save();

        $this->assertEquals(1, Plan::all()
                                   ->count());
    }

    public function test_i_can_get_the_plans_schedule()
    {
        $plan = $this->createMonthlyPlan()
                     ->getObject();

        $this->assertTrue($plan->schedule instanceof \Sinclair\Schedulable\Models\Schedule);
    }

    public function test_whether_a_plan_has_a_schedule()
    {
        $plan = $this->createMonthlyPlan()
                     ->getObject();

        $this->assertTrue($plan->hasSchedule());
    }

    public function test_a_plan_does_not_have_a_schedule()
    {
        $plan = $this->makePlanWithoutSchedule();

        $this->assertFalse($plan->hasSchedule());
    }

    public function test_i_should_be_able_to_get_theoretical_last_run_date()
    {
        $plan = $this->createMonthlyPlan()
                     ->getObject();

        $this->assertTrue($plan->previous() instanceof \Carbon\Carbon);
    }

    public function test_i_should_be_able_to_get_theoretical_next_run_date()
    {
        $plan = $this->createMonthlyPlan()
                     ->getObject();

        $this->assertTrue($plan->next() instanceof \Carbon\Carbon);
    }

    public function test_a_schedule_is_due()
    {
        $dt = \Carbon\Carbon::now();

        $plan = $this->createMonthlyPlan($dt)
                     ->getObject();

        $this->assertTrue($plan->isDue($dt));
    }

    public function test_a_schedule_is_not_due()
    {
        $dt = \Carbon\Carbon::now();

        $plan = $this->createMonthlyPlan($dt)
                     ->getObject();

        $this->assertFalse($plan->isDue($dt->addWeek()));
    }

    public function test_a_schedules_last_run_timestamp_is_updated_when_it_is_run()
    {
        $dt = \Carbon\Carbon::now();

        $plan = $this->createMonthlyPlan($dt)
                     ->getObject();

        $this->assertNull($plan->schedule->last_ran_at);

        $plan->hasRun();

        $this->assertNotNull($plan->schedule->last_ran_at);
    }

    public function test_a_schedules_next_run_timestamp_is_updated_when_it_is_run()
    {
        $dt = \Carbon\Carbon::now();

        $plan = $this->createMonthlyPlan($dt)
                     ->getObject();

        $plan->hasRun();

        $this->assertEquals($dt->addMonth()->second(0), $plan->schedule->next_runs_at);
    }

    public function test_i_can_get_the_next_3_dates_the_schedule_will_run()
    {
        $dt = \Carbon\Carbon::now();

        $plan = $this->createMonthlyPlan($dt)
                     ->getObject();

        $this->assertEquals(3, $plan->nextRunDates(3)
                                    ->count());
    }

    public function test_i_can_get_the_previous_3_dates_the_schedule_will_run()
    {
        $dt = \Carbon\Carbon::now();

        $plan = $this->createMonthlyPlan($dt)
                     ->getObject();

        $this->assertEquals(3, $plan->previousRunDates(3)
                                    ->count());
    }
}