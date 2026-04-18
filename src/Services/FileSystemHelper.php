<?php

namespace Mehedi8gb\ApiCrudify\Services;

class FileSystemHelper
{
    /**
     * Create directory for the file if it does not exist.
     */
    public function createDirectoryIfNotExists(string $fileName): void
    {
        $directory = dirname($fileName);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Check if file exists.
     */
    public function isFileExists(string $fileName): bool
    {
        return file_exists($fileName);
    }
}
