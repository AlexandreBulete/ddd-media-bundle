<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Storage;

use AlexandreBulete\DddMediaBundle\Domain\Service\FileInspectorInterface;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\FileFacts;

/**
 * Type from the bytes (fileinfo), dimensions from the image header: nothing
 * the uploader's browser claims is trusted.
 */
final class NativeFileInspector implements FileInspectorInterface
{
    public function inspect(string $localFile): FileFacts
    {
        if (!is_file($localFile) || !is_readable($localFile)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a readable file.', $localFile));
        }

        $mimeType = (new \finfo(\FILEINFO_MIME_TYPE))->file($localFile);
        $size = filesize($localFile);
        $facts = new FileFacts(is_string($mimeType) ? $mimeType : 'application/octet-stream', $size === false ? 0 : $size);

        if (!$facts->isImage()) {
            return $facts;
        }

        $dimensions = @getimagesize($localFile);
        if ($dimensions === false || $dimensions[0] < 1 || $dimensions[1] < 1) {
            return $facts;
        }

        return new FileFacts($facts->mimeType, $facts->size, $dimensions[0], $dimensions[1]);
    }
}
