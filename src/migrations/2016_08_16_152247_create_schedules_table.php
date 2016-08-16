<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function ( Blueprint $table )
        {
            $table->increments('id');
            $table->morphs('schedulable');
            $table->integer('minute');
            $table->integer('hour');
            $table->integer('day_of_week');
            $table->integer('day_of_month');
            $table->integer('month_of_year');
            $table->integer('year');
            $table->boolean('is_last_day_of_month');
            $table->boolean('is_adhoc');
            $table->boolean('is_minutely');
            $table->boolean('is_hourly');
            $table->boolean('is_daily');
            $table->boolean('is_weekly');
            $table->boolean('is_monthly');
            $table->boolean('is_annually');
            $table->integer('frequency_n');
            $table->dateTime('starts_at');
            $table->dateTime('expires_at');
            $table->dateTime('last_ran_at');
            $table->softDeletes();
            $table->timestamps();

            $table->index([ 'schedulable_type', 'schedulable_id' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('schedules');
    }
}
