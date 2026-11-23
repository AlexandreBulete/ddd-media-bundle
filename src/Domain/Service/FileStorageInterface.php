<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\Service;

/**
 * Where the files live: a local disk today, an object storage tomorrow. Paths
 * are relative, separated by "/".
 */
interface FileStorageInterface
{
    /**
     * Copies a local file (an upload's temporary file) to the storage.
     */
    public function store(string $path, string $localFile): void;

    /**
     * Deleting a file already gone is not an error.
     */
    public function delete(string $path): void;
}
