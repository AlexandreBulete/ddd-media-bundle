<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\Service;

use AlexandreBulete\DddMediaBundle\Domain\Exception\UnsupportedFileTypeException;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\FileFacts;

interface FileInspectorInterface
{
    /**
     * Reads type, size and dimensions from the file's content.
     *
     * @throws UnsupportedFileTypeException
     */
    public function inspect(string $localFile): FileFacts;
}
