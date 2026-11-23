<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class MediaStored implements DomainEvent
{
    public function __construct(
        public string $mediaId,
        public string $path,
        public string $mimeType,
        public int $size,
    ) {}
}
