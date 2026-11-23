<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Query\FindMediaLibrary;

use AlexandreBulete\DddFoundation\Application\Handler\QueryCollectionHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;

/**
 * @extends QueryCollectionHandler<Media>
 */
#[AsQueryHandler]
final readonly class FindMediaLibraryHandler extends QueryCollectionHandler
{
    public function __construct(MediaRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return RepositoryInterface<Media>
     */
    public function __invoke(FindMediaLibraryQuery $query): RepositoryInterface
    {
        return $this->build($query);
    }
}
