<?php namespace Delphinium\Poppies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateDelphiniumPoppiesPopquizzes extends Migration
{
    public function up()
    {
        Schema::table('delphinium_poppies_popquizzes', function($table)
        {
			$table->string('questions',1024);//could be long
			$table->string('game_style',255);
			$table->integer('course_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('delphinium_poppies_popquizzes', function($table)
        {
			$table->dropColumn('questions');
			$table->dropColumn('game_style');
			$table->dropColumn('course_id');
        });
    }
}
