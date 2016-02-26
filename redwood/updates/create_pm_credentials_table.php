<?php namespace Delphinium\Redwood\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePMCredentialsTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_redwood_pm_credentials') )
        {
            Schema::create('delphinium_redwood_pm_credentials', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name');
                $table->string('client_id');
                $table->string('client_secret');
                $table->string('key');
                $table->string('secret');
                $table->string('workspace');
                $table->string('server_url');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_redwood_pm_credentials') )
        {
            Schema::drop('delphinium_redwood_pm_credentials');
        }
    }

}
