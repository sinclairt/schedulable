<?php

namespace Sinclair\Schedulable\Providers;

use Illuminate\Support\ServiceProvider;
use Sinclair\Schedulable\Contracts\Schedule as ScheduleInterface;
use Sinclair\Schedulable\Contracts\ScheduleFactory as ScheduleFactoryInterface;
use Sinclair\Schedulable\Models\Schedule as ScheduleModel;
use Sinclair\Schedulable\Services\ScheduleFactory as ScheduleFactoryService;

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