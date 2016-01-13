<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class DropGradesTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('delphinium_blossom_grades');
    }

    public functio down()
    {
        if ( !Schema::hasTable('delphinium_blossom_grades') )
        {
            Schema::create('delphinium_blossom_grades', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name');
                $table->string('Size');
                $table->timestamps();
            });
        }
    }
}
