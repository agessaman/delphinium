<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateGradesTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_blossom_grades') )
        {
            Schema::create('delphinium_blossom_grades', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name');
                $table->string('Animate');
                $table->string('Size');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_blossom_grades') )
        {
            Schema::dropIfExists('delphinium_blossom_grades');
        }
    }

}
