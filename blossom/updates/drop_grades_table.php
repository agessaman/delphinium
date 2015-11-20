<?php namespace Delphinium\Blossom\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateGradesTable extends Migration
{

    public function up()
    {
        Schema::dropIfExists('delphinium_blossom_grades');
    }

}
