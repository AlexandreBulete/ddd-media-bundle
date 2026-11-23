<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Imagine;

use AlexandreBulete\DddMediaBundle\Domain\Event\MediaRemoved;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * A removed media takes its resized copies with it: they are public files,
 * and would stay reachable by their URL.
 */
final readonly class PurgeResizedCopies
{
    public function __construct(
        private CacheManager $cacheManager,
    ) {}

    #[AsEventListener]
    public function __invoke(MediaRemoved $event): void
    {
        $this->cacheManager->remove($event->path);
    }
}
