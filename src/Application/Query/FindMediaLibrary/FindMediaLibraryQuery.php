<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Query\FindMediaLibrary;

use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;

/**
 * The library, for the back office: filtered, sorted, paginated.
 *
 * @implements QueryInterface<MediaRepositoryInterface>
 */
final readonly class FindMediaLibraryQuery implements QueryInterface
{
    /**
     * @param array<string, mixed>  $criteria
     * @param array<string, string> $withSorting
     */
    public function __construct(
        public ?int $page = null,
        public ?int $itemsPerPage = null,
        public array $criteria = [],
        public array $withSorting = [],
    ) {}
}
