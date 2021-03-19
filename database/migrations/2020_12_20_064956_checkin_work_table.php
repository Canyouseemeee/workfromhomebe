<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CheckinWorkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkin_work', function (Blueprint $table) {
            $table->bigIncrements('checkinid');
            $table->integer('userid');
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->date('date_in');
            $table->integer('status');
            $table->string('file');
            $table->string('latitude');
            $table->string('longitude');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkin_work');
    }
}
