<?php namespace Delphinium\Irisnew\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDelphiniumIrisnewCreateChartTable extends Migration
{
    public function up()
    {
        Schema::create('delphinium_irisnew_create_chart_table', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('delphinium_irisnew_create_chart_table');
    }
}
