<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Command\RemoveMedia;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

/**
 * Refused while something shows the file: replace it there first.
 *
 * @implements CommandInterface<void>
 */
final readonly class RemoveMediaCommand implements CommandInterface
{
    public function __construct(
        public MediaId $id,
    ) {}
}
