<?php namespace Delphinium\Raspberry\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCacheTable extends Migration
{

    public function up()
    {
     	if (!Schema::hasTable('octobercms.cache') )
    	{
        	Schema::create('octobercms.cache', function($table)
        	{
            	$table->engine = 'InnoDB';
                $table->string('key')->unique();
    			$table->text('value');
    			$table->integer('expiration');
       	 	});
       	 }
    }

    public function down()
    {
	    if ( Schema::hasTable('octobercms.cache') )
    	{
        	Schema::drop('octobercms.cache');
    	}
    }

}
