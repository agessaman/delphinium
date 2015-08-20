<?php

namespace Delphinium\Blade\Classes\Rules;

interface IPersistent {
    public function delete();
    public function findOrCreate();
    public function exists();
}

