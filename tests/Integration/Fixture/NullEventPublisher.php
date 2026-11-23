<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Tests\Integration\Fixture;

use AlexandreBulete\DddMediaBundle\Domain\Service\DomainEventPublisherInterface;

final class NullEventPublisher implements DomainEventPublisherInterface
{
    public function publishAll(iterable $events): void {}
}
