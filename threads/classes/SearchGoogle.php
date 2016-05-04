<?php

namespace Delphinium\Threads\Classes;

class SearchGoogle extends \Thread
{
    public function __construct($query)
    {
        $this->query = $query;
    }

    public function run()
    {
        $this->html = file_get_contents('http://google.fr?q='.$this->query);
    }
}