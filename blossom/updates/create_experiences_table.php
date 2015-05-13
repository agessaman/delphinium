<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateExperiencesTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_blossom_experiences') )
        {
            Schema::create('delphinium_blossom_experiences', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Name');
                $table->string('Maximum');
                $table->string('Milestones');
                $table->string('Start Date');
                $table->string('End Date');
                $table->string('Animate');
                $table->string('Size');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
         if ( Schema::hasTable('delphinium_blossom_experiences') )
        {
            Schema::dropIfExists('delphinium_blossom_experiences');
        }
    }

}
