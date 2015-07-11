<?php

namespace Delphinium\Blade\Classes\Rules;

/**
 * Interface for actions that can be executed on rules
 *
 * @author Daniel
 */
interface Action extends ISavable {
    public function execute(Context $ctx);
}
