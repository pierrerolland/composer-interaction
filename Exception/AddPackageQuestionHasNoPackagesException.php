<?php

namespace RollAndRock\Exception;

class AddPackageQuestionHasNoPackagesException extends \Exception
{
    protected $message = 'A question with action "add-package" should have a "packages" property';
}
