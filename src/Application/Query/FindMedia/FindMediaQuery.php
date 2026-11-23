<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Query\FindMedia;

use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

/**
 * @implements QueryInterface<Media>
 */
final readonly class FindMediaQuery implements QueryInterface
{
    public function __construct(
        public MediaId $id,
    ) {}
}
