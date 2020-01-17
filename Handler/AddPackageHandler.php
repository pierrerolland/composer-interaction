<?php

namespace RollAndRock\Handler;

use RollAndRock\Util\Executor;

class AddPackageHandler
{
    private Executor $executor;

    public function __construct(Executor $executor = null)
    {
        $this->executor = is_null($executor) ? new Executor() : $executor;
    }

    public function addPackages(array $packages)
    {
        $toInstall = [];

        foreach ($packages as $name => $version) {
            $toInstall[] = $version === '*' ? $name : sprintf('%s:%s', $name, $version);
        }

        $this->executor->execute(sprintf('%s require %s', $_SERVER['argv'][0], implode(' ', $toInstall)));
    }
}
