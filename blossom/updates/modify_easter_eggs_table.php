<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class ModifyEasterEggsTable extends Migration
{

    public function up()
    {
        Schema::table('delphinium_blossom_easter_eggs', function ($table) {
            $table->dropColumn('course_id');
            $table->dropColumn('copy_id');
        });
    }

    public function down()
    {
        Schema::table('delphinium_blossom_easter_eggs', function ($table) {
            $table->integer('course_id')->nullable();
            $table->integer('copy_id')->nullable();
        });
    }

}
