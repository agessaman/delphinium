<?php namespace Delphinium\Blossom\Updates;
use Schema;
use October\Rain\Database\Updates\Migration;
class CreateCompetenciesTable extends Migration
{
    //http://octobercms.com/docs/database/structure
    public function up()
    {
		Schema::create('delphinium_blossom_competencies', function($table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->string('Name');
			$table->boolean('Animate');//tinyInt switch 0~1 true false
			$table->string('Size');//Small,Medium,Large radio btns
			$table->timestamps();
		});
    }
    public function down()
    {
		Schema::dropIfExists('delphinium_blossom_competencies');
    }
}
