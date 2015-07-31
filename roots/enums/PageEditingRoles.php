<?php namespace Delphinium\Roots\Enums;

use Delphinium\Roots\Enums\BasicEnum;

abstract class PageEditingRoles extends BasicEnum {
    const TEACHERS = "Teachers";
    const STUDENTS = "Students";
    const MEMBERS = "Members";
    const GENERAL_PUBLIC = "Public";
}