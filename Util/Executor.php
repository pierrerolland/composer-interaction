<?php

namespace RollAndRock\Util;

class Executor
{
    public function execute(string $cmd): void
    {
        shell_exec($cmd);
    }
}
