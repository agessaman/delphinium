<?php namespace Delphinium\Roots\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Delphinium\Roots\Models\User;

class ModifyUsersTable extends Migration
{
    function up()
    {
        Schema::table('delphinium_roots_users', function($table)
        {//add the avatar and names columns
            $table->string('avatar')->nullable();
            $table->string('name');
            $table->string('sortable_name');
            
        });
        
        Schema::table('delphinium_roots_users', function($table)
        {//make the encrypted token nullable so that we can add the students-> we won't be storing their tokens
            $table->dropColumn(array('encrypted_token','course_id'));
            
        });
    }

    function down()
    {
        
        Schema::table('delphinium_roots_users', function($table)
        {//drop the avatar and names columns
            $table->dropColumn(array('avatar', 'name', 'sortable_name'));
        });
        
        Schema::table('delphinium_roots_users', function($table)
        {//make the encrypted_token not nullable
            $table->longText('encrypted_token')->nullable();
            $table->string('course_id');
        });
        
    }
}