<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateConfigurationsTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_blossom_configurations') )
        {
            Schema::create('delphinium_blossom_configurations', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_blossom_configurations') )
        {
            Schema::dropIfExists('delphinium_blossom_configurations');
        }
    }

}
