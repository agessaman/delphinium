<?php namespace Delphinium\Roots\Updates;

use Delphinium\Roots\Models\Role;
use October\Rain\Database\Updates\Seeder;

class SeedRolesTable extends Seeder
{
    public function run()
    {
        Role::create([
            'role_name' => 'Learner'
        ]);
        
        Role::create([
            'role_name' => 'TeachingAssistant'
        ]);
        
        Role::create([
            'role_name' => 'Instructor'
        ]);
        
        Role::create([
            'role_name' => 'Approver'
        ]);
    }
}
