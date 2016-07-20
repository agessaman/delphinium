<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddSnowToEggs extends Migration
{

    public function up()
    {
        Schema::table('delphinium_blossom_easter_eggs', function ($table) {
            $table->string('snow');
        });
    }

    public function down()
    {
        Schema::table('delphinium_blossom_easter_eggs', function ($table) {
            $table->dropColumn('snow');
        });
    }

}
