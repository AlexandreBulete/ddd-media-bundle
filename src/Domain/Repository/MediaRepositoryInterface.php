<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\Repository;

use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

/**
 * @extends RepositoryInterface<Media>
 */
interface MediaRepositoryInterface extends RepositoryInterface
{
    public function save(Media $media): void;

    public function remove(Media $media): void;

    /**
     * In the order of the identifiers given; unknown ones are left out.
     *
     * @param list<MediaId> $ids
     *
     * @return list<Media>
     */
    public function findByIds(array $ids): array;
}
