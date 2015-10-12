<?php namespace Acme\Blog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::table('delphinium_roots_assignments', function ($table) {
            $table->integer('position')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('delphinium_roots_assignments', function ($table) {
            $table->dropColumn('position');
        });
    }
}