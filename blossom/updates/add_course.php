<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Delphinium\Roots\Models\User;

class AddCourse extends Migration
{
    public function up()
    {
        Schema::table('delphinium_blossom_stats', function($table)
        {
            $table->integer('course_id')->nullable()->unsigned();
        });

        Schema::table('delphinium_blossom_experiences', function($table)
        {
            $table->integer('course_id')->nullable()->unsigned();
        });
    }

    public function down()
    {
        Schema::table('delphinium_blossom_stats', function($table)
        {
            $table->dropColumn(array('course_id'));
        });
        Schema::table('delphinium_blossom_experiences', function($table)
        {
            $table->dropColumn(array('course_id'));
        });
    }
}