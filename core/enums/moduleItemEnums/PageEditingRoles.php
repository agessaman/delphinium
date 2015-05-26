<?php namespace Delphinium\Core\Enums\ModuleItemEnums;

use Delphinium\Core\Enums\CommonEnums\BasicEnum;

abstract class PageEditingRoles extends BasicEnum {
    const TEACHERS = "Teachers";
    const STUDENTS = "Students";
    const MEMBERS = "Members";
    const GENERAL_PUBLIC = "Public";
}