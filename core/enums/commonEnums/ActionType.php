<?php namespace Delphinium\Core\Enums\CommonEnums;

abstract class ActionType extends BasicEnum {
    const GET = 0;
    const POST = 1;
    const PUT = 2;
    const DELETE = 3;
}