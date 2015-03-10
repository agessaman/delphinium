<?php namespace Delphinium\Core\Updates;

use Seeder;
use Delphinium\Core\Models\CacheSetting;

class SeedCacheSettingsTable extends Seeder
{
    public function run()
    {
        CacheSetting::create([
            'cache_setting_id' => 1,
            'data_type'        => 'Modules',
            'time'             => -1,
            'created_at'       => \Carbon\Carbon::now()->toDateTimeString()
        ]);
        
        CacheSetting::create([
            'cache_setting_id' => 2,
            'data_type'        => 'Assignments',
            'time'             => 5,
            'created_at'       => \Carbon\Carbon::now()->toDateTimeString()
        ]);
    }
}