<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCompetenciesTable extends Migration
{
    //http://octobercms.com/docs/database/structure
    public function up()
    {
        if ( !Schema::hasTable('delphinium_blossom_competencies') )
        {
            Schema::create('delphinium_blossom_competencies', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name');
				$table->string('Color');//hex #FF00FF
                $table->boolean('Animate');//tinyInt switch 0~1 true false
                $table->string('Size');//Small,Medium,Large radio btns
				$table->integer('course_id');
                $table->string('copy_id');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_blossom_competencies') )
        {
            Schema::dropIfExists('delphinium_blossom_competencies');
        }
    }

}
