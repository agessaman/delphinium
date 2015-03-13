<?php namespace Delphinium\Core\Enums\ModuleItemEnums;

use Delphinium\Core\Enums\CommonEnums\BasicEnum;

abstract class CompletionRequirementType extends BasicEnum {
    const MUST_VIEW = "MUST_VIEW";
    const MUST_CONTRIBUTE = "MUST_CONTRIBUTE";
    const MUST_SUBMIT = "MUST_SUBMIT";
}