<?php namespace Delphinium\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCoreModulesTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_core_modules') )
    	{
            Schema::create('delphinium_core_modules', function($table)
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
                $table->timestamps();
            });
       	 }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_core_modules') )
    	{
            Schema::drop('delphinium_core_modules');
    	}
    }

}
