<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCompetenciesTable extends Migration
{
    public function up()
    {
		// ditch any existing and start fresh?
            Schema::dropIfExists('delphinium_blossom_competencies');
			
			Schema::create('delphinium_blossom_competencies', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name');
				$table->string('Color');
                $table->string('Animate');// tinyInt switch 0~1 true false
                $table->string('Size');//Small,Medium,Large radio btns
				$table->string('course_id');
                $table->timestamps();
            });
		/*
		//http://www.w3schools.com/sql/sql_alter.asp
			Schema::table('delphinium_blossom_competencies', function($table)
            {
				$table->string('Color');
				$table->string('course_id');
            });
		*/
    }

    public function down()
    {
		Schema::table('delphinium_blossom_competencies', function($table)
        {
            //$table->dropColumn('Color');
			$table->dropColumn('course_id');
        });
    }
}
