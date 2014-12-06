<?php namespace OctoDevel\OctoSlider\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateItemsTable extends Migration
{

    public function up()
    {
        // Create octodevel_octoslider_items table
        if ( !Schema::hasTable('octodevel_octoslider_itemss') )
        {
            Schema::create('octodevel_octoslider_items', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Drop octodevel_octoslider_items table
        if ( Schema::hasTable('octodevel_octoslider_items') )
        {
            Schema::drop('octodevel_octoslider_items');
        }
    }

}
