<?php
/**
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

namespace Delphinium\Roots\Updates;

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