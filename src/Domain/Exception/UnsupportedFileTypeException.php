<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\Exception;

/**
 * A file of a type the library does not keep: a document, an archive, an
 * image format browsers cannot all display.
 */
final class UnsupportedFileTypeException extends \DomainException
{
    public function __construct(
        public readonly string $mimeType,
    ) {
        parent::__construct(sprintf('Files of type "%s" are not accepted.', $mimeType));
    }
}
