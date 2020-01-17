<?php

namespace RollAndRock\Exception;

class FileNotFoundException extends \Exception
{
    protected $message = 'File not found for replacement.';
}
