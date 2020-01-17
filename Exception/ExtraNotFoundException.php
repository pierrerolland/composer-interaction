<?php

namespace RollAndRock\Exception;

class ExtraNotFoundException extends \Exception
{
    protected $message = 'The interaction plugin needs an "extra" key. See the README for further information.';
}
