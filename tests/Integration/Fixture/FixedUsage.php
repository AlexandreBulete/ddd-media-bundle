<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Tests\Integration\Fixture;

use AlexandreBulete\DddMediaBundle\Domain\Service\MediaUsageInterface;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

/**
 * Says a given media is used, by whatever label it was given.
 */
final class FixedUsage implements MediaUsageInterface
{
    /** @var array<string, list<string>> */
    public array $usages = [];

    public function usagesOf(MediaId $id): array
    {
        return $this->usages[(string) $id] ?? [];
    }
}
