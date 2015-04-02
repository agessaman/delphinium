<?php namespace Delphinium\Core\Enums\ModuleItemEnums;

use Delphinium\Core\Enums\CommonEnums\BasicEnum;

abstract class CompletionRequirementType extends BasicEnum {
    const MUST_VIEW = "must_view";
    const MUST_CONTRIBUTE = "must_contribute";
    const MUST_SUBMIT = "must_submit";
}