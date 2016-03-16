<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateEasterEggsTable extends Migration
{

    public function up()
    {
        if ( Schema::hasTable('delphinium_blossom_easter_eggs') )
        {
            Schema::dropIfExists('delphinium_blossom_easter_eggs');
            Schema::create('delphinium_blossom_easter_eggs', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->string('menu');
                $table->integer('course_id')->nullable();
                $table->integer('copy_id')->nullable();
                $table->timestamps();
            });
        }
        else
        {
            Schema::create('delphinium_blossom_easter_eggs', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->string('menu');
                $table->integer('course_id')->nullable();
                $table->integer('copy_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_blossom_easter_eggs') )
        {
            Schema::dropIfExists('delphinium_blossom_easter_eggs');
            Schema::create('delphinium_blossom_easter_eggs', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name');
                $table->integer('course_id')->nullable();
                $table->integer('copy_id')->nullable();
                $table->timestamps();
            });
        }
    }

}
