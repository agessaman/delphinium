<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Delphinium\Roots\Models\User;

class MoveAliasUsersCourse extends Migration
{
    function up()
    {
        //drop the alias from general users table
        Schema::table('delphinium_roots_users', function($table)
        {//make the encrypted token nullable so that we can add the students-> we won't be storing their tokens
            $table->dropColumn(array('avatar'));

        });

        //attach alias and avatar to users_course table
        Schema::table('delphinium_roots_users_course', function($table)
        {//add the avatar and names columns
            $table->string('avatar')->nullable();
            $table->string('alias')->nullable();

        });

    }

    function down()
    {
        Schema::table('delphinium_roots_users', function($table)
        {//make the encrypted_token not nullable
            $table->string('avatar')->nullable();
        });


        Schema::table('delphinium_roots_users_course', function($table)
        {//drop the avatar and names columns
            $table->dropColumn(array('avatar', 'alias'));
        });
    }
}