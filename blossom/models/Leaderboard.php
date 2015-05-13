<?php namespace Delphinium\Blossom\Models;

use Model;

/**
 * leaderboard Model
 */
class Leaderboard extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'delphinium_blossom_leaderboards';

    public $rules = [
        'Name'=>'required',
        'Animate'=>'required',
        'Size' => 'required'
    ];

}