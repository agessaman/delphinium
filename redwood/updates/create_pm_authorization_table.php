<?php namespace Delphinium\Redwood\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePMAuthorizationTable extends Migration
{

    public function up()
    {
        if ( !Schema::hasTable('delphinium_pm_authorization') )
        {
            Schema::create('delphinium_pm_authorization', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('workspace');
                $table->longText('encrypted_access_token');
                $table->longText('encrypted_refresh_token');
                $table->integer('expires_in')->unsigned();
                $table->string('token_type');
                $table->string('scope');

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if ( Schema::hasTable('delphinium_pm_authorization') )
        {
            Schema::drop('delphinium_pm_authorization');
        }
    }

}
