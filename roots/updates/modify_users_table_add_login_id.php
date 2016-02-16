<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class ModifyUsersTableAddLoginId extends Migration
{
    function up()
    {
        //attach alias and avatar to users_course table
        Schema::table('delphinium_roots_users', function($table)
        {//add the avatar and names columns
            $table->integer('sis_login_id')->nullable();

        });

    }

    function down()
    {
        Schema::table('delphinium_roots_users', function($table)
        {//drop the avatar and names columns
            $table->dropColumn(array('sis_login_id'));
        });
    }
}