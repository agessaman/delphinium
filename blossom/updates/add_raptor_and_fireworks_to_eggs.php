<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddRaptorAndFireworksToEggs extends Migration
{

    public function up()
    {
        Schema::table('delphinium_blossom_easter_eggs', function ($table) {
            $table->string('raptor');
            $table->string('fireworks');
        });
    }

    public function down()
    {
        Schema::table('delphinium_blossom_easter_eggs', function ($table) {
            $table->dropColumn('raptor');
            $table->dropColumn('fireworks');
        });
    }

}
