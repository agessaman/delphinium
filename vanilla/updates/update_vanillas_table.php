<?php namespace Delphinium\Vanilla\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateVanillasTable extends Migration
{
    public function up()
    {
        Schema::table('delphinium_vanilla_vanillas', function($table)
        {
			$table->string('course_id',255);
        });
    }

    public function down()
    {
		$table->dropColumn('course_id');
    }
}
