<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Delphinium\Roots\Models\User;

class ModifyAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::table('delphinium_roots_assignments', function($table)
        {
            $table->float('points_possible')->change();
        });
    }

    public function down()
    {
        Schema::table('delphinium_roots_assignments', function($table)
        {
            $table->integer('points_possible')->change();
        });
    }
}