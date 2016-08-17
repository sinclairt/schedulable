<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulesTable extends Migration
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
            $table->integer('minute')->nullable();
            $table->integer('hour')->nullable();
            $table->integer('day_of_week')->nullable();
            $table->integer('day_of_month')->nullable();
            $table->integer('month_of_year')->nullable();
            $table->integer('year')->nullable();
            $table->boolean('is_last_day_of_month')->default(0);
            $table->boolean('is_adhoc')->default(0);
            $table->boolean('is_minutely')->default(0);
            $table->boolean('is_hourly')->default(0);
            $table->boolean('is_daily')->default(0);
            $table->boolean('is_weekly')->default(0);
            $table->boolean('is_monthly')->default(0);
            $table->boolean('is_annually')->default(0);
            $table->integer('frequency_n')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('last_ran_at')->nullable();
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
