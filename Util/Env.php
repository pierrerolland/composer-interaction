<?php

namespace RollAndRock\Util;

use RollAndRock\Exception\FileNotFoundException;
use Symfony\Component\Dotenv\Dotenv;

class Env
{
    private FileManager $fileManager;
    private Dotenv $dotenv;

    public function __construct(FileManager $fileManager = null, Dotenv $dotenv = null)
    {
        $this->fileManager = is_null($fileManager) ? new FileManager() : $fileManager;
        $this->dotenv = is_null($dotenv) ? new Dotenv() : $dotenv;
    }

    public function read(): array
    {
        try {
            return $this->dotenv->parse($this->fileManager->read('.env'));
        } catch (FileNotFoundException $e) {
            return [];
        }
    }
}
