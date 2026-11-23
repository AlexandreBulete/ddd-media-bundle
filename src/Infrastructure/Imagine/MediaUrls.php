<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Imagine;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddMediaBundle\Application\Query\FindMediaByIds\FindMediaByIdsQuery;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Contracts\Service\ResetInterface;

/**
 * From a media id (what other contexts hold) to a URL a page can print.
 *
 * Media are remembered for the request: call preload() with every id a page
 * shows and the page costs one query, whatever the number of pictures.
 * Without preload(), each unknown id is looked up on its own.
 */
final class MediaUrls implements ResetInterface
{
    /** @var array<string, Media|null> id => media, null when unknown */
    private array $known = [];

    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly CacheManager $cacheManager,
    ) {}

    /**
     * @param iterable<MediaId|string|\Stringable|null> $ids
     */
    public function preload(iterable $ids): void
    {
        $missing = [];
        foreach ($ids as $id) {
            $id = self::idOf($id);
            if ($id !== null && !array_key_exists((string) $id, $this->known)) {
                $missing[(string) $id] = $id;
                $this->known[(string) $id] = null;
            }
        }
        if ($missing === []) {
            return;
        }

        /** @var list<Media> $found */
        $found = $this->queryBus->ask(new FindMediaByIdsQuery(array_values($missing)));
        foreach ($found as $media) {
            $this->known[(string) $media->id] = $media;
        }
    }

    public function media(Media|MediaId|string|\Stringable|null $media): ?Media
    {
        if ($media instanceof Media || $media === null) {
            return $media;
        }

        $id = self::idOf($media);
        if ($id === null) {
            return null;
        }
        $this->preload([$id]);

        return $this->known[(string) $id] ?? null;
    }

    /**
     * The URL of the picture through a LiipImagine filter: resized on first
     * request, served as a static file afterwards. Null when there is none.
     */
    public function url(Media|MediaId|string|\Stringable|null $media, string $filter): ?string
    {
        $media = $this->media($media);
        if ($media === null || !$media->isImage()) {
            return null;
        }

        return $this->cacheManager->getBrowserPath($media->path, $filter);
    }

    public function reset(): void
    {
        $this->known = [];
    }

    private static function idOf(MediaId|string|\Stringable|null $id): ?MediaId
    {
        if ($id === null || $id instanceof MediaId) {
            return $id;
        }

        try {
            return MediaId::fromString((string) $id);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }
}
