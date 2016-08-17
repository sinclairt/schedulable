<?php

namespace Wtbi\Schedulable\Providers;

use Illuminate\Support\ServiceProvider;
use Wtbi\Schedulable\Contracts\Schedule as ScheduleInterface;
use Wtbi\Schedulable\Contracts\ScheduleFactory as ScheduleFactoryInterface;
use Wtbi\Schedulable\Models\Schedule as ScheduleModel;
use Wtbi\Schedulable\Services\ScheduleFactory as ScheduleFactoryService;

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
        $this->app->bind(ScheduleInterface::class, ScheduleModel::class);
        $this->app->bind('Schedule', ScheduleInterface::class);

        $this->app->bind(ScheduleFactoryInterface::class, ScheduleFactoryService::class);
        $this->app->bind('ScheduleFactory', ScheduleFactoryInterface::class);
    }
}