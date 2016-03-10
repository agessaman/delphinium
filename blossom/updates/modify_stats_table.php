<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Delphinium\Roots\Models\User;

class ModifyStatsTable extends Migration
{
    public function up()
    {
        Schema::table('delphinium_blossom_stats', function($table)
        {
            $table->string('size')->change();
        });
    }

    public function down()
    {
        Schema::table('delphinium_blossom_stats', function($table)
        {
            $table->integer('points_possible')->change();
        });
    }
}