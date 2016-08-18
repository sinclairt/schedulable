<?php

require_once 'DbTestCase.php';
require_once 'Models/Plan.php';

class ScheduleFactoryTest extends DbTestCase
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

    public function test_the_helper_returns_an_instance_of_schedule_factory()
    {
        $this->assertTrue(schedule() instanceof \Wtbi\Schedulable\Services\ScheduleFactory);
    }

    public function test_the_factory_loads_when_an_object_is_supplied_at_construct_without_a_schedule()
    {
        $this->assertTrue(schedule($this->makePlanWithoutSchedule()) instanceof \Wtbi\Schedulable\Services\ScheduleFactory);
    }

    public function test_the_factory_loads_when_an_object_is_supplied_at_construct_with_a_schedule()
    {
        $factory = schedule($this->makePlanWithSchedule([
            'minute'       => 0,
            'hour'         => 0,
            'day_of_month' => 1,
            'is_monthly'   => true,
        ]));

        $this->assertTrue($factory instanceof \Wtbi\Schedulable\Services\ScheduleFactory);

        $this->assertTrue($factory->minute() == 0);

        $this->assertTrue($factory->hour() == 0);

        $this->assertTrue($factory->dayOfMonth() == 1);

        $this->assertTrue($factory->isMonthly() == 1);
    }

    public function test_i_can_set_a_property()
    {
        $factory = schedule($this->makePlanWithSchedule([
            'minute'       => 0,
            'hour'         => 0,
            'day_of_month' => 1,
            'is_monthly'   => true,
        ]));

        $this->assertTrue($factory->minute() == 0);

        $factory->minute(10);

        $this->assertTrue($factory->minute() == 10);
    }

    public function test_i_can_get_a_property()
    {
        $factory = schedule($this->makePlanWithSchedule([
            'minute'       => 0,
            'hour'         => 0,
            'day_of_month' => 1,
            'is_monthly'   => true,
        ]));

        $this->assertTrue($factory->minute() == 0);
    }

    public function test_the_overloaded_call_only_returns_protected_properties()
    {
        $factory = schedule($this->makePlanWithSchedule([
            'minute'       => 0,
            'hour'         => 0,
            'day_of_month' => 1,
            'is_monthly'   => true,
        ]));

        $this->assertTrue($factory->minute() == 0);

        $this->setExpectedException(Exception::class, 'object is not a property');

        $factory->object();
    }

    public function test_i_can_change_the_type_of_schedule()
    {
        $factory = schedule($this->makePlanWithSchedule([
            'minute'       => 0,
            'hour'         => 0,
            'day_of_month' => 1,
            'is_monthly'   => true,
        ]));

        $this->assertTrue($factory->isMonthly() == 1);

        $factory->daily();

        $this->assertFalse($factory->isMonthly());

        $this->assertTrue($factory->isDaily());
    }

    public function test_i_can_get_the_object()
    {
        $this->assertTrue(schedule($this->makePlanWithoutSchedule())->getObject() instanceof Plan);
    }

    public function test_i_can_set_the_object()
    {
        $factory = schedule()->setObject($this->makePlanWithoutSchedule());

        $this->assertTrue($factory instanceof \Wtbi\Schedulable\Services\ScheduleFactory);

        $this->assertTrue($factory->getObject()->name == 'test_plan');
    }

    public function test_i_can_change_the_object()
    {
        $factory = schedule($this->makePlanWithSchedule([
            'minute'       => 0,
            'hour'         => 0,
            'day_of_month' => 1,
            'is_monthly'   => true,
        ]));

        $this->assertTrue($factory instanceof \Wtbi\Schedulable\Services\ScheduleFactory);

        $this->assertTrue($factory->getObject()->name == 'test_plan');

        $plan = new Plan([ 'name' => 'another_test_plan' ]);

        $plan->save();

        $factory->setObject($plan);

        $this->assertTrue($factory->getObject()->name == 'another_test_plan');
    }

    public function test_when_i_change_the_object_i_can_reload_its_attached_schedule()
    {
        $factory = schedule($this->makePlanWithSchedule([
            'minute'       => 0,
            'hour'         => 0,
            'day_of_month' => 1,
            'is_monthly'   => true,
        ]));

        $this->assertTrue($factory instanceof \Wtbi\Schedulable\Services\ScheduleFactory);

        $this->assertTrue($factory->getObject()->name == 'test_plan');

        $plan = new Plan([ 'name' => 'another_test_plan' ]);

        $plan->save();

        $plan->schedule()
             ->create([
                 'minute'   => 0,
                 'hour'     => 0,
                 'is_daily' => true,
             ]);

        $factory->setObject($plan);

        $this->assertTrue($factory->getObject()->name == 'another_test_plan');

        $factory->load();

        $this->assertTrue($factory->dayOfMonth() == null);

        $this->assertTrue($factory->isMonthly() == 0);

        $this->assertTrue($factory->isDaily() == 1);
    }

    public function test_i_can_load_from_a_schedule()
    {
        $factory = schedule($this->makePlanWithoutSchedule());

        $this->assertFalse($factory->isMonthly());

        $plan = $this->makePlanWithSchedule([
            'minute'       => 0,
            'hour'         => 0,
            'day_of_month' => 1,
            'is_monthly'   => true,
        ]);

        $factory->loadFromSchedule($plan->schedule);

        $this->assertTrue($factory->isMonthly() == 1);
    }

    public function test_i_can_load_from_a_cron_using_hourly()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('@hourly');

        $this->assertTrue($factory->minute() == 0);
        $this->assertNull($factory->hour());
        $this->assertNull($factory->dayOfWeek());
        $this->assertNull($factory->dayOfMonth());
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertTrue($factory->isHourly());
        $this->assertFalse($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_load_from_a_cron_using_hourly_expression()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('0 * * * *');

        $this->assertTrue($factory->minute() == 0);
        $this->assertNull($factory->hour());
        $this->assertNull($factory->dayOfWeek());
        $this->assertNull($factory->dayOfMonth());
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertTrue($factory->isHourly());
        $this->assertFalse($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_load_from_a_cron_using_daily()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('@daily');

        $this->assertTrue($factory->minute() == 0);
        $this->assertTrue($factory->hour() == 0);
        $this->assertNull($factory->dayOfWeek());
        $this->assertNull($factory->dayOfMonth());
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertTrue($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_load_from_a_cron_using_daily_expression()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('0 0 * * *');

        $this->assertTrue($factory->minute() == 0);
        $this->assertTrue($factory->hour() == 0);
        $this->assertNull($factory->dayOfWeek());
        $this->assertNull($factory->dayOfMonth());
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertTrue($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_load_from_a_cron_using_weekly()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('@weekly');

        $this->assertTrue($factory->minute() == 0);
        $this->assertTrue($factory->hour() == 0);
        $this->assertTrue($factory->dayOfWeek() == 0);
        $this->assertNull($factory->dayOfMonth());
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertFalse($factory->isDaily());
        $this->assertTrue($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_load_from_a_cron_using_weekly_expression()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('0 0 * * 0');

        $this->assertTrue($factory->minute() == 0);
        $this->assertTrue($factory->hour() == 0);
        $this->assertTrue($factory->dayOfWeek() == 0);
        $this->assertNull($factory->dayOfMonth());
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertFalse($factory->isDaily());
        $this->assertTrue($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_load_from_a_cron_using_monthly()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('@monthly');

        $this->assertTrue($factory->minute() == 0);
        $this->assertTrue($factory->hour() == 0);
        $this->assertNull($factory->dayOfWeek());
        $this->assertTrue($factory->dayOfMonth() == 1);
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertFalse($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertTrue($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_load_from_a_cron_using_monthly_expression()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('0 0 1 * *');

        $this->assertTrue($factory->minute() == 0);
        $this->assertTrue($factory->hour() == 0);
        $this->assertNull($factory->dayOfWeek());
        $this->assertTrue($factory->dayOfMonth() == 1);
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertFalse($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertTrue($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_load_from_a_cron_using_annually()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('@annually');

        $this->assertTrue($factory->minute() == 0);
        $this->assertTrue($factory->hour() == 0);
        $this->assertNull($factory->dayOfWeek());
        $this->assertTrue($factory->dayOfMonth() == 1);
        $this->assertTrue($factory->monthOfYear() == 1);
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertFalse($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertTrue($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_load_from_a_cron_using_annually_expression()
    {
        $factory = schedule($this->makePlanWithoutSchedule())->loadFromCron('0 0 1 1 *');

        $this->assertTrue($factory->minute() == 0);
        $this->assertTrue($factory->hour() == 0);
        $this->assertNull($factory->dayOfWeek());
        $this->assertTrue($factory->dayOfMonth() == 1);
        $this->assertTrue($factory->monthOfYear() == 1);
        $this->assertNull($factory->year());
        $this->assertFalse($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertFalse($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertTrue($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());
    }

    public function test_i_can_set_the_schedule()
    {
        $factory = schedule($this->makePlanWithoutSchedule());

        $this->assertFalse($factory->getSchedule()->exists);

        $plan = $this->makePlanWithSchedule([
            'minute'   => 0,
            'hour'     => 0,
            'is_daily' => true,
        ]);

        $factory->setSchedule($plan->schedule);

        $this->assertTrue($factory->getSchedule()->exists);
    }

    public function test_i_can_get_the_schedule()
    {
        $this->assertTrue(schedule($this->makePlanWithSchedule([
                'minute'   => 0,
                'hour'     => 0,
                'is_daily' => true,
            ]))->getSchedule() instanceof \Wtbi\Schedulable\Contracts\Schedule);
    }

    public function test_i_can_create_a_schedule_fluently_where_an_object_does_not_have_a_schedule()
    {
        $factory = schedule($this->makePlanWithoutSchedule())
            ->hourly()
            ->minute(5)
            ->save();

        $this->assertTrue($factory->minute() == 5);
        $this->assertNull($factory->hour());
        $this->assertNull($factory->dayOfWeek());
        $this->assertNull($factory->dayOfMonth());
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertTrue($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertFalse($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());

        $this->assertTrue($factory->getSchedule()->exists);
    }

    public function test_i_can_create_a_schedule_fluently_where_an_object_does_have_a_schedule()
    {
        $factory = schedule($this->makePlanWithSchedule([
            'minute'   => 0,
            'hour'     => 0,
            'is_daily' => true,
        ]));

        $this->assertTrue($factory->minute() == 0);
        $this->assertTrue($factory->hour() == 0);
        $this->assertNull($factory->dayOfWeek());
        $this->assertNull($factory->dayOfMonth());
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertTrue($factory->isHourly() == 0);
        $this->assertTrue($factory->isLastDayOfMonth() == 0);
        $this->assertTrue($factory->isDaily() == 1);
        $this->assertFalse($factory->isWeekly() == 1);
        $this->assertFalse($factory->isMonthly() == 1);
        $this->assertFalse($factory->isAnnually() == 1);
        $this->assertFalse($factory->isQuarterly() == 1);
        $this->assertFalse($factory->isAdhoc() == 1);

        $factory->resetSchedule()
                ->hourly()
                ->minute(5)
                ->save();

        $this->assertTrue($factory->minute() == 5);
        $this->assertNull($factory->hour());
        $this->assertNull($factory->dayOfWeek());
        $this->assertNull($factory->dayOfMonth());
        $this->assertNull($factory->monthOfYear());
        $this->assertNull($factory->year());
        $this->assertTrue($factory->isHourly());
        $this->assertFalse($factory->isLastDayOfMonth());
        $this->assertFalse($factory->isDaily());
        $this->assertFalse($factory->isWeekly());
        $this->assertFalse($factory->isMonthly());
        $this->assertFalse($factory->isAnnually());
        $this->assertFalse($factory->isQuarterly());
        $this->assertFalse($factory->isAdhoc());

        $schedule = $factory->getSchedule();

        $this->assertTrue($schedule->exists);
        $this->assertTrue($schedule->minute == 5);
        $this->assertNull($schedule->hour);
        $this->assertNull($schedule->day_of_week);
        $this->assertNull($schedule->day_of_month);
        $this->assertNull($schedule->month_of_year);
        $this->assertNull($schedule->year);
        $this->assertTrue($schedule->is_hourly == 1);
        $this->assertTrue($schedule->is_last_day_of_month == 0);
        $this->assertTrue($schedule->is_daily == 0);
        $this->assertTrue($schedule->is_weekly == 0);
        $this->assertTrue($schedule->is_monthly == 0);
        $this->assertTrue($schedule->is_annually == 0);
        $this->assertTrue($schedule->is_quarterly == 0);
        $this->assertTrue($schedule->is_adhoc == 0);
    }

    public function test_an_invalid_hourly_plan()
    {
        $this->setExpectedException(Exception::class, 'The following fields need to be set: minute');
        schedule(new Plan([ 'name' => 'hourly' ]))
            ->hourly()
            ->save();
    }

    public function test_an_invalid_daily_plan()
    {
        $this->setExpectedException(Exception::class, 'The following fields need to be set: hour, minute');
        schedule(new Plan([ 'name' => 'daily' ]))
            ->daily()
            ->save();
    }

    public function test_an_invalid_weekly_plan()
    {
        $this->setExpectedException(Exception::class, 'The following fields need to be set: day_of_week, hour, minute');
        schedule(new Plan([ 'name' => 'weekly' ]))
            ->weekly()
            ->save();
    }

    public function test_an_invalid_monthly_plan()
    {
        $this->setExpectedException(Exception::class, 'The following fields need to be set: day_of_month, hour, minute');
        schedule(new Plan([ 'name' => 'monthly' ]))
            ->monthly()
            ->save();
    }

    public function test_an_invalid_annual_plan()
    {
        $this->setExpectedException(Exception::class, 'The following fields need to be set: month_of_year, day_of_month, hour, minute');
        schedule(new Plan([ 'name' => 'annually' ]))
            ->annually()
            ->save();
    }

    public function test_an_invalid_adhoc_plan()
    {
        $this->setExpectedException(Exception::class, 'The following fields need to be set: year, month_of_year, day_of_month, hour, minute');
        schedule(new Plan([ 'name' => 'adhoc' ]))
            ->adhoc()
            ->save();
    }
}