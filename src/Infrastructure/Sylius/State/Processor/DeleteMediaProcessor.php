<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddMediaBundle\Application\Command\RemoveMedia\RemoveMediaCommand;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

final readonly class DeleteMediaProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        Assert::isInstanceOf($data, MediaResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);

        $this->commandBus->dispatch(new RemoveMediaCommand(MediaId::fromUlid($data->id)));

        return null;
    }
}
