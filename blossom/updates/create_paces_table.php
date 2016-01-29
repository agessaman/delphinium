<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePacesTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_blossom_paces') )
        {
            Schema::create('delphinium_blossom_paces', function($table)
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
        if ( Schema::hasTable('delphinium_blossom_paces') )
        {
            Schema::dropIfExists('delphinium_blossom_paces');
        }
    }

}
