<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddMediaBundle\Application\Query\FindMedia\FindMediaQuery;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResource;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResourceFactory;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class MediaItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private MediaResourceFactory $factory,
    ) {}

    public function provide(Operation $operation, Context $context): ?MediaResource
    {
        $id = $context->get(RequestOption::class)?->request()->attributes->getString('id');
        if ($id === null || $id === '') {
            return null;
        }

        /** @var Media $media */
        $media = $this->queryBus->ask(new FindMediaQuery(MediaId::fromString($id)));

        return $this->factory->fromModel($media);
    }
}
