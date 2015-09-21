<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateExperiencesTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_blossom_experiences') )
        {
            Schema::create('delphinium_blossom_experiences', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->string('total_points');
                $table->dateTime('start_date');
                $table->dateTime('end_date');
                $table->float('bonus_per_day');
                $table->float('penalty_per_day');
                $table->integer('bonus_days');
                $table->integer('penalty_days');
                $table->boolean('animate');
                $table->string('size');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
         if ( Schema::hasTable('delphinium_blossom_experiences') )
        {
            Schema::dropIfExists('delphinium_blossom_experiences');
        }
    }

}
