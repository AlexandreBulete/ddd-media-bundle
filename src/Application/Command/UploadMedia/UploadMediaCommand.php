<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Command\UploadMedia;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

/**
 * Adds a file to the library. The id is chosen by the caller, so that it can
 * point at the media (a cover, a visual) in the same breath.
 *
 * `$localFile` is a readable local path: an upload's temporary file.
 *
 * @implements CommandInterface<Media>
 */
final readonly class UploadMediaCommand implements CommandInterface
{
    public function __construct(
        public MediaId $id,
        public string $localFile,
        public string $originalName,
        public ?string $alt = null,
    ) {}
}
