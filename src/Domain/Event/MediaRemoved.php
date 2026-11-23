<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * The file is gone from the storage too: listeners clear what derives from it
 * (resized copies, CDN caches).
 */
final readonly class MediaRemoved implements DomainEvent
{
    public function __construct(
        public string $mediaId,
        public string $path,
    ) {}
}
