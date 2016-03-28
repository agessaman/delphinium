<?php namespace Delphinium\Orchid\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateQuizlessonsTable extends Migration
{

    public function up()
    {
        Schema::table('delphinium_orchid_quizlessons', function($table)
        {
            $table->string('quiz_name',255);
            $table->string('quiz_id',255);
            $table->string('course_id',255);
        });
    }

    public function down()
    {
        Schema::table('delphinium_poppies_popquizzes', function($table)
        {
            $table->dropColumn('quiz_name');
            $table->dropColumn('quiz_id');
            $table->dropColumn('course_id');
        });
    }

}
