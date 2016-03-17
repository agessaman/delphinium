<?php namespace Delphinium\Poppies\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDelphiniumPoppiesPopquizzes extends Migration
{
    public function up()
    {
        Schema::table('delphinium_poppies_popquizzes', function($table)
        {
            $table->integer('course_id');
            $table->string('copy_id', 255);
        });
    }
    
    public function down()
    {
        Schema::table('delphinium_poppies_popquizzes', function($table)
        {
            $table->dropColumn('course_id');
            $table->dropColumn('copy_id');
        });
    }
}
