<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Application\Command\DescribeMedia;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class DescribeMediaHandler
{
    public function __construct(
        private MediaRepositoryInterface $repository,
        private ClockInterface $clock,
    ) {}

    public function __invoke(DescribeMediaCommand $command): Media
    {
        $media = $this->repository->findById($command->id)
            ?? throw new EntityNotFoundException(Media::class, $command->id);

        $media->describe($command->alt, $this->clock->now());
        $this->repository->save($media);

        return $media;
    }
}
