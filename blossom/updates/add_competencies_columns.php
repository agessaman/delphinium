<?php namespace Delphinium\Blossom\Updates;
use Schema;
use October\Rain\Database\Updates\Migration;
class AddCompetenciesTable extends Migration
{
    public function up()
    {
		//http://octobercms.com/docs/database/structure	
		Schema::table('delphinium_blossom_competencies', function($table)
		{
			$table->string('Color');//hex #FF00FF
		});
    }
    public function down()
    {
        Schema::table('delphinium_blossom_competencies', function($table)
        {
            $table->dropColumn('Color');
        });
    }
}