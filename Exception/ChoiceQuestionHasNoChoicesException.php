<?php

namespace RollAndRock\Exception;

class ChoiceQuestionHasNoChoicesException extends \Exception
{
    protected $message = 'Questions with type "choice" must have a non-empty "choices" property.';
}
