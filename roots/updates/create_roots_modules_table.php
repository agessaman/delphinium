<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRootsModulesTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_roots_modules') )
    	{
            Schema::create('delphinium_roots_modules', function($table)
            {
                $table->engine = 'InnoDB';
                $table->integer('module_id');
                $table->integer('course_id');
                $table->string('name');
                $table->integer('position')->nullable();
                $table->dateTime('unlock_at')->nullable();
                $table->boolean('require_sequential_progress')->nullable();
                $table->boolean('publish_final_grade')->nullable();
                $table->string('prerequisite_module_ids')->nullable();
                $table->string('state')->nullable();
                $table->boolean('published')->nullable();
                $table->integer('items_count')->nullable();
                $table->boolean('locked');
                
                //this data is used for ordering the modules. It comes from the iris manager, not from Canvas API
                $table->integer('order');//the position this module will occupy in its parent
                $table->integer('parent_id');
                $table->timestamps();
            });
       	 }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_roots_modules') )
    	{
            Schema::drop('delphinium_roots_modules');
    	}
    }

}
