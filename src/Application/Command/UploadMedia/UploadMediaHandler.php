<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Command\UploadMedia;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;
use AlexandreBulete\DddMediaBundle\Domain\Service\FileInspectorInterface;
use AlexandreBulete\DddMediaBundle\Domain\Service\FileStorageInterface;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class UploadMediaHandler
{
    public function __construct(
        private MediaRepositoryInterface $repository,
        private FileInspectorInterface $inspector,
        private FileStorageInterface $storage,
        private ClockInterface $clock,
    ) {}

    public function __invoke(UploadMediaCommand $command): Media
    {
        $facts = $this->inspector->inspect($command->localFile);
        $now = $this->clock->now();
        // One folder per month keeps directories small; the id keeps names unique.
        $path = sprintf('%s/%s.%s', $now->format('Y/m'), strtolower((string) $command->id), $facts->extension());

        $media = Media::store($command->id, $path, $command->originalName, $facts, $command->alt, $now);
        $this->storage->store($path, $command->localFile);
        try {
            $this->repository->save($media);
        } catch (\Throwable $e) {
            // No row, no file: an orphan file is a leak nobody would see.
            $this->storage->delete($path);

            throw $e;
        }

        return $media;
    }
}
