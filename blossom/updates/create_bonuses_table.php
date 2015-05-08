<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBonusesTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_blossom_bonuses') )
        {
            Schema::create('delphinium_blossom_bonuses', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name');
                $table->string('Component');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_blossom_bonuses') )
        {
            Schema::dropIfExists('delphinium_blossom_bonuses');
        }
    }

}
