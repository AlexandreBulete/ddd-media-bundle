<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddMediaBundle\Application\Command\UploadMedia\UploadMediaCommand;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResource;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResourceFactory;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Webmozart\Assert\Assert;

final readonly class CreateMediaProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private MediaResourceFactory $factory,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): MediaResource
    {
        Assert::isInstanceOf($data, MediaResource::class);
        Assert::isInstanceOf($data->file, UploadedFile::class);

        /** @var Media $media */
        $media = $this->commandBus->dispatch(new UploadMediaCommand(
            id: MediaId::generate(),
            localFile: $data->file->getPathname(),
            originalName: $data->file->getClientOriginalName(),
            alt: $data->alt,
        ));

        return $this->factory->fromModel($media);
    }
}
