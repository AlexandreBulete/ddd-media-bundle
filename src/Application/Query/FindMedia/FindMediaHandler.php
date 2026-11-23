<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Query\FindMedia;

use AlexandreBulete\DddFoundation\Application\Handler\QuerySingleHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;

/**
 * @extends QuerySingleHandler<Media>
 */
#[AsQueryHandler]
final readonly class FindMediaHandler extends QuerySingleHandler
{
    public function __construct(MediaRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function __invoke(FindMediaQuery $query): Media
    {
        return $this->build($query);
    }
}
