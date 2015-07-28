<?php namespace Delphinium\Roots\Enums;

use Delphinium\Roots\Enums\BasicEnum;

abstract class ModuleItemType extends BasicEnum {
    const FILE = "File";
    const PAGE = "Page";
    const DISCUSSION = "Discussion";
    const ASSIGNMENT = "Assignment";
    const QUIZ = "Quiz";
    const SUBHEADER = "SubHeader";
    const EXTERNALURL = "ExternalUrl";
    const EXTERNALTOOL = "ExternalTool";
}