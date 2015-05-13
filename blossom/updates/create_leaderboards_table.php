<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateLeaderboardsTable extends Migration
{

    public function up()
    {
         if ( !Schema::hasTable('delphinium_blossom_leaderboards') )
        {
            Schema::create('delphinium_blossom_leaderboards', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name');
                $table->string('Animate');
                $table->string('Size');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_blossom_leaderboards') )
        {
            Schema::dropIfExists('delphinium_blossom_leaderboards');
        }
    }

}
