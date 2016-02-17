<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCompetenciesTable extends Migration
{

    public function up()
    {
			Schema::table('delphinium_blossom_competencies', function($table)
            {
				$table->string('Color');
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
