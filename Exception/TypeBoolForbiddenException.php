<?php

namespace RollAndRock\Exception;

class TypeBoolForbiddenException extends \Exception
{
    protected $message = 'Type bool is forbidden in "replace" actions.';
}
