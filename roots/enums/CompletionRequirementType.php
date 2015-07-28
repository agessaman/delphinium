<?php namespace Delphinium\Roots\Enums;

use Delphinium\Roots\Enums\BasicEnum;

abstract class CompletionRequirementType extends BasicEnum {
    const MUST_VIEW = "must_view";
    const MUST_CONTRIBUTE = "must_contribute";
    const MUST_SUBMIT = "must_submit";
}