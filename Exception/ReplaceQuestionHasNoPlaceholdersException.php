<?php

namespace RollAndRock\Exception;

class ReplaceQuestionHasNoPlaceholdersException extends \Exception
{
    protected $message = 'A question with action "replace" should have a "placeholders" property';
}
