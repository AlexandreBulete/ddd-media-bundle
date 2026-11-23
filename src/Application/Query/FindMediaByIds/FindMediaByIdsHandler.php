<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Query\FindMediaByIds;

use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;

#[AsQueryHandler]
final readonly class FindMediaByIdsHandler
{
    public function __construct(
        private MediaRepositoryInterface $repository,
    ) {}

    /**
     * @return list<Media>
     */
    public function __invoke(FindMediaByIdsQuery $query): array
    {
        return $this->repository->findByIds($query->ids);
    }
}
