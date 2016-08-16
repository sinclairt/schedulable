<?php

namespace Wtbi\Schedulable\Providers;

use Illuminate\Support\ServiceProvider;

class SchedulableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../../migrations/' => base_path('/database/migrations'),
        ]);
    }

    public function register()
    {
        $this->app->bind(\Wtbi\Schedulable\Contracts\Schedule::class, \Wtbi\Schedulable\Models\Schedule::class);
        $this->app->bind('Schedule', \Wtbi\Schedulable\Contracts\Schedule::class);

        $this->app->bind(\Wtbi\Schedulable\Contracts\ScheduleFactory::class, \Wtbi\Schedulable\Services\ScheduleFactory::class);
        $this->app->bind('ScheduleFactory', \Wtbi\Schedulable\Contracts\ScheduleFactory::class);
    }
}