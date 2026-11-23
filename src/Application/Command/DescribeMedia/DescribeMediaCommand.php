<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Command\DescribeMedia;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

/**
 * @implements CommandInterface<Media>
 */
final readonly class DescribeMediaCommand implements CommandInterface
{
    public function __construct(
        public MediaId $id,
        public ?string $alt,
    ) {}
}
