<?php

namespace RollAndRock\Handler;

use RollAndRock\Util\Env;
use RollAndRock\Util\FileManager;

class EnvHandler
{
    private Env $env;
    private FileManager $fileManager;

    public function __construct(Env $env = null, FileManager $fileManager = null)
    {
        $this->env = is_null($env) ? new Env() : $env;
        $this->fileManager = is_null($fileManager) ? new FileManager() : $fileManager;
    }

    public function addEnv(array $env): void
    {
        if (empty($env)) {
            return;
        }

        $values = array_merge($this->env->read(), $env);
        $envValues = [];
        foreach ($values as $key => $val) {
            array_push($envValues, sprintf('%s=%s', $key, $val));
        }

        $this->fileManager->write('.env', implode(PHP_EOL, $envValues), true);
    }
}
