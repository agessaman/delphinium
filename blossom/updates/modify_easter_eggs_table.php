<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class ModifyEasterEggsTable extends Migration
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
                $table->string('harlem_shake');
                $table->string('ripples');
                $table->string('asteroids');
                $table->string('katamari');
                $table->string('bombs');
                $table->string('ponies');
                $table->string('my_little_pony');
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
                $table->string('harlem_shake');
                $table->string('ripples');
                $table->string('asteroids');
                $table->string('katamari');
                $table->string('bombs');
                $table->string('ponies');
                $table->string('my_little_pony');
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
                $table->string('name');
                $table->string('menu');
                $table->string('harlem_shake');
                $table->string('ripples');
                $table->string('asteroids');
                $table->string('katamari');
                $table->string('bombs');
                $table->string('ponies');
                $table->string('my_little_pony');
                $table->integer('course_id')->nullable();
                $table->integer('copy_id')->nullable();
                $table->timestamps();
            });
        }
    }

}
