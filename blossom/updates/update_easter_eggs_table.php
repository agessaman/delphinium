<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateEasterEggsTable extends Migration
{

    public function up()
    {
        Schema::table('delphinium_blossom_easter_eggs', function ($table) {
            $table->renameColumn('Name', 'name');
            $table->string('menu');
            $table->string('harlem_shake');
            $table->string('ripples');
            $table->string('asteroids');
            $table->string('katamari');
            $table->string('bombs');
            $table->string('ponies');
            $table->string('my_little_pony');
        });
    }

    public function down()
    {
        Schema::table('delphinium_blossom_easter_eggs', function ($table) {
            $table->renameColumn('name', 'Name');
            $table->dropColumn('menu');
            $table->dropColumn('harlem_shake');
            $table->dropColumn('ripples');
            $table->dropColumn('asteroids');
            $table->dropColumn('katamari');
            $table->dropColumn('bombs');
            $table->dropColumn('ponies');
            $table->dropColumn('my_little_pony');
        });
    }

}
