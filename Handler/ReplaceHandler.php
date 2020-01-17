<?php

namespace RollAndRock\Handler;

use RollAndRock\Util\FileManager;

class ReplaceHandler
{
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager = null)
    {
        $this->fileManager = is_null($fileManager) ? new FileManager() : $fileManager;
    }

    public function replace(string $file, string $placeholder, string $replacement): void
    {
        $this->fileManager->write(
            $file,
            str_replace($placeholder, $replacement, $this->fileManager->read($file))
        );
    }
}
