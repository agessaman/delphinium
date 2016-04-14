<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Delphinium\Roots\Models\User;

class ModifyContentTable extends Migration
{
    public function up()
    {
        Schema::table('delphinium_roots_content', function($table)
        {
            $table->string('lock_explanation', 1000)->change();
        });
    }

    public function down()
    {
        Schema::table('delphinium_roots_content', function($table)
        {
            $table->string('lock_explanation', 255)->change();
        });
    }
}