<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDelphiniumBlossomAttendanceSessions extends Migration
{
    public function up()
    {
        Schema::create('delphinium_blossom_attendance_sessions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('course_id')->unsigned();
            $table->integer('assignment_id')->unsigned();
            $table->string('title', 255);
            $table->dateTime('start_at');
            $table->integer('duration_minutes')->unsigned();
            $table->string('code', 255);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('delphinium_blossom_attendance_sessions');
    }
}
