<?php

namespace RollAndRock\Util;

use RollAndRock\Exception\FileNotFoundException;

class FileManager
{
    public function read(string $filename): string
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException();
        }

        return file_get_contents($filename);
    }

    public function write(string $filename, string $data, $withCreate = false): void
    {
        if (!$withCreate && !file_exists($filename)) {
            throw new FileNotFoundException();
        }

        file_put_contents($filename, $data);
    }
}
