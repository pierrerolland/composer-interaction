<?php

namespace RollAndRock\Exception;

class UnknownQuestionTypeException extends \Exception
{
    protected $message = 'A question has an unknown question type. Type must be one of "free", "bool", or "choices".';
}
