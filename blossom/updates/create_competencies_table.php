<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCompetenciesTable extends Migration
{

    public function up()
    {
        Schema::create('delphinium_blossom_competencies', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delphinium_blossom_competencies');
    }

}
