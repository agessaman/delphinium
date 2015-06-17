<?php namespace Delphinium\Roots\Enums\ModuleItemEnums;

use Delphinium\Roots\Enums\CommonEnums\BasicEnum;

abstract class CompletionRequirementType extends BasicEnum {
    const MUST_VIEW = "must_view";
    const MUST_CONTRIBUTE = "must_contribute";
    const MUST_SUBMIT = "must_submit";
}