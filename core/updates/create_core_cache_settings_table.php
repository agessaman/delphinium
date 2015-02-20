<?php namespace Delphinium\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCoreCacheSettingsTable extends Migration
{

    public function up()
    {
     	if ( !Schema::hasTable('delphinium_core_cache_settings') )
    	{
        	Schema::create('delphinium_core_cache_settings', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->bigIncrements('cache_setting_id');
                $table->integer('time');
                /*NOTE: 
                 * if time = -1, data will be cached forever
                 * if time = 0, data will NOT be cached
                 * if time >0, data will be cached for that many minutes
                 */
                $table->string('data_type');
                $table->timestamps();
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('delphinium_core_cache_settings') )
    	{
        	Schema::drop('delphinium_core_cache_settings');
    	}
    }

}
