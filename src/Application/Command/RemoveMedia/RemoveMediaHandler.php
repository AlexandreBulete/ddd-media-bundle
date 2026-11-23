<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Command\RemoveMedia;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddMediaBundle\Domain\Exception\MediaInUseException;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;
use AlexandreBulete\DddMediaBundle\Domain\Service\FileStorageInterface;
use AlexandreBulete\DddMediaBundle\Domain\Service\MediaUsageInterface;

#[AsCommandHandler]
final readonly class RemoveMediaHandler
{
    /**
     * @param iterable<MediaUsageInterface> $usages
     */
    public function __construct(
        private MediaRepositoryInterface $repository,
        private FileStorageInterface $storage,
        private iterable $usages,
    ) {}

    public function __invoke(RemoveMediaCommand $command): void
    {
        $media = $this->repository->findById($command->id)
            ?? throw new EntityNotFoundException(Media::class, $command->id);

        $usages = [];
        foreach ($this->usages as $usage) {
            array_push($usages, ...$usage->usagesOf($media->id));
        }
        if ($usages !== []) {
            throw new MediaInUseException($media->originalName, $usages);
        }

        $media->remove();
        $this->repository->remove($media);
        // After the row: a file without a row is invisible, a row without its
        // file is a broken picture.
        $this->storage->delete($media->path);
    }
}
