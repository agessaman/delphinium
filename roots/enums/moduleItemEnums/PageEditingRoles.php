<?php namespace Delphinium\Roots\Enums\ModuleItemEnums;

use Delphinium\Roots\Enums\CommonEnums\BasicEnum;

abstract class PageEditingRoles extends BasicEnum {
    const TEACHERS = "Teachers";
    const STUDENTS = "Students";
    const MEMBERS = "Members";
    const GENERAL_PUBLIC = "Public";
}