<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Query\FindMediaByIds;

use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

/**
 * The media a page shows, all at once: a listing of twelve covers is one
 * query, not twelve. Public: a media holds nothing secret, only what a page
 * already displays.
 *
 * @implements QueryInterface<list<Media>>
 */
#[PublicAccess]
final readonly class FindMediaByIdsQuery implements QueryInterface
{
    /**
     * @param list<MediaId> $ids
     */
    public function __construct(
        public array $ids,
    ) {}
}
