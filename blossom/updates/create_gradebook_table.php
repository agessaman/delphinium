<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateGradebookTable extends Migration
{
    public function up()
    {
        if ( !Schema::hasTable('delphinium_blossom_gradebook') )
        {
            Schema::create('delphinium_blossom_gradebook', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->string('name', 255);
                $table->boolean('animate');
                $table->string('size', 255);
                $table->timestamp('created_at')->default('0000-00-00 00:00:00');
                $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
                $table->integer('course_id')->nullable()->unsigned();
            });
        }
    }
    
    public function down()
    {
        if ( Schema::hasTable('delphinium_blossom_gradebook') )
        {
            Schema::dropIfExists('delphinium_blossom_gradebook');
        }
    }
}
