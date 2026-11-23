<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Storage;

use AlexandreBulete\DddMediaBundle\Domain\Service\FileStorageInterface;
use League\Flysystem\FilesystemOperator;

/**
 * The library's files on a Flysystem storage: `media.storage` unless
 * `media.storage` points elsewhere in the bundle configuration.
 */
final readonly class FlysystemFileStorage implements FileStorageInterface
{
    public function __construct(
        private FilesystemOperator $filesystem,
    ) {}

    public function store(string $path, string $localFile): void
    {
        $stream = fopen($localFile, 'r');
        if ($stream === false) {
            throw new \RuntimeException(sprintf('Cannot read "%s".', $localFile));
        }

        try {
            $this->filesystem->writeStream($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function delete(string $path): void
    {
        // Flysystem v3: deleting a missing file is a no-op.
        $this->filesystem->delete($path);
    }
}
