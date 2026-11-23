<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Infrastructure\Imagine\MediaUrls;

/**
 * A resource with its thumbnail, through the bundle's own filter.
 */
final readonly class MediaResourceFactory
{
    public const string THUMBNAIL_FILTER = 'media_admin_thumbnail';

    public function __construct(
        private MediaUrls $urls,
    ) {}

    public function fromModel(Media $media): MediaResource
    {
        return MediaResource::fromModel($media, $this->urls->url($media, self::THUMBNAIL_FILTER));
    }
}
