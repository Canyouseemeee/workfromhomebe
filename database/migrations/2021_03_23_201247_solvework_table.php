<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SolveworkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solvework', function (Blueprint $table) {
            $table->bigIncrements('solveworkid');
            \$table->integer('taskid');
            $table->integer('createsolvework');
            $table->String('subject');
            $table->integer('statussolvework');
            $table->integer('deparmentid');
            $table->String('assignment');
            $table->String('file');
            $table->dateTime('assign_date');
            $table->dateTime('due_date');
            $table->date('close_date');
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
        Schema::dropIfExists('solvework');
    }
}
