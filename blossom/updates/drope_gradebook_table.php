<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class DropGradebookTable extends Migration
{
    public function up()
    {
        if ( Schema::hasTable('delphinium_blossom_gradebook') )
        {
            Schema::dropIfExists('delphinium_blossom_gradebook');
        }
    }
    
    public function down()
    {
        
    }
}