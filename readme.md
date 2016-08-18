#Schedulable

###Installation
* Make sure you have access to the WTBI team inside Bitbucket. 
* Make sure your have this code block inside your `composer.json` file:
```
"repositories": [
    {
      "type": "composer",
      "url": "http://satis.wtbidev.co.uk"
    }
  ]
```
* Run `composer require wtbi/schedulable`.
* Register `Wtbi\Schedulable\Providers\SchedulableServiceProvider::class` in `config\app.php` inside the `providers` array.
* Run `composer dump-autoload`
* Run `php artisan vendor:publish`. This wil publish the migration for the schedules.
* Run `php artisan migrate`

###Usage
Use the `IsSchedulable` trait inside your model. This will give you access to a host of scopes and methods. 
#####Create Schedules
Use the helper method to start building your schedules fluently.
```
schedule($object)->daily()->hour(10)->minute(0)->save();
```
#####Update Schedules
If your object already has a schedule attached, then when you make changes, and save, the changes will be made to the existing schedule.

#####Available Fields
```
 minutely()
 hourly()
 daily()
 weekly()
 monthly()
 annually()
 quarterly()
 adhoc()
 isMinutely()
 isHourly()
 isDaily()
 isWeekly()
 isMonthly()
 isAnnually()
 isQuarterly()
 isAdhoc()
 minute( $value = null )
 hour( $value = null )
 dayOfWeek( $value = null )
 dayOfMonth( $value = null )
 monthOfYear( $value = null )
 year( $value = null )
 isLastDayOfMonth( $value = null )
 frequencyN( $value = null )
 startsAt( $value = null )
 expiresAt( $value = null )
```
 
######Dynamic setting/getting
This factory uses dynamic methods, so be aware, for example,  calling `schedule($object)->hour()` will return the current hour stored on the schedule factory, however, calling `schedule($object)->hour(10)` will set the hour property to `10` and return the factory for you to keep chaining.

######Starts At/Expires At
You can set when a schedule should start and end, this is optional. The active/expired scopes will take notice of these fields.

######Shortcuts
`loadFromSchedule($schedule = null)` - this will load all the attributes from the supplied schedule into the factory. It will also set the factory's schedule object to the schedule supplied. If no schedule is supplied then it will use the currently attached schedule i.e.
 ```
 schedule($objectWithoutSchedule)->loadFromSchedule($anotherSchedule);
 // or
 schedule($objectWithoutSchedule)->setSchedule($schedule)->loadFromSchedule();
 ```
 
 `resetSchedule()` - will reset all the properties to their default regardless of the object(s) attached to the factory.
 
 `refresh()` - will return a new instance of the factory with the current object inside it.
 
 `loadFromCron($string)` - will set the properties from a cron expression to the factory. You can use pseudo expressions:
 * @annually
 * @monthly
 * @weekly
 * @daily
 * @hourly

#####Getting dates
You can get the `next()` date, `previous()` date, `nextRunDates($n)`, `previousRunDates($nth)` (where `$n` is the number of dates you need i.e. 3).
You can also get the potential run dates between two dates as well with `runDatesBetween(Carbon $from, Carbon $to, bool $active = true)`.

#####Running schedules
When a schedule has run it is a good idea to run the `hasRun()` method, because this will update the `last_run_at` and `next_runs_at` timestamps.
 
#####Scopes
There a huge number of scopes available to you:
```

dueOn( Carbon $dt, $active = true )
isNow()
isMinutely()
isHourly()
isDaily()
isWeekly()
isMonthly()
isAnnually()
isQuarterly()
isAdhoc()
between( Carbon $dtFrom, Carbon $dtTo = null, $active = true )
day( Carbon $dt )
dayBetween( Carbon $dtFrom, Carbon $dtTo = null )
dayOfMonth( int $day )
dayOfMonthBetween( Carbon $dtFrom, Carbon $dtTo = null )
dayOfWeek( int $day )
dayOfWeekBetween( Carbon $dtFrom, Carbon $dtTo = null )
lastOfMonth( Carbon $dt )
month( int $month )
monthBetween( int $from, int $to )
year( int $year )
yearBetween( int $from, int $to )
hour( int $hour )
hourBetween( int $from, int $to )
minute( int $minute )
minuteBetween( int $from, int $to )
isActive( Carbon $dt = null )
isActiveBetween( Carbon $dtFrom, Carbon $dtTo = null )
isExpired( Carbon $dt = null )
```

###Disclaimer
Although a lot of time and effort has gone into making the <i>between</i> scopes and methods, it does have its limitations, as the dates are calculated on the fly, so be careful when using them, as they could cause a memory failure in your script.

###Other Methods
* `hasSchedule()` - determines whether your object has a schedule attached
* `isDue( Carbon $dt = null )` - is the schedule due on a specific date, or if `$dt` is blank now.
* `getScheduleType( $schedule )` - (on the Schedule model)
* `allRunDatesBetween( Carbon $dtFrom, Carbon $dtTo = null, $active = true )` - (on the Schedule model) - all dates that all the schedules could run.
* `setSchedule()` - (ScheduleFactory)
* `getSchedule()` - (ScheduleFactory)
* `setObject()` - (ScheduleFactory)
* `getObject()` - (ScheduleFactory)
* `load()` - (ScheduleFactory) - this will take the current objects schedule and set all the properties.