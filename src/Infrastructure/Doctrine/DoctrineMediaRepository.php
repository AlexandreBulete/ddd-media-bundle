<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;
use AlexandreBulete\DddMediaBundle\Domain\Service\DomainEventPublisherInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends DoctrineRepository<Media>
 */
final class DoctrineMediaRepository extends DoctrineRepository implements MediaRepositoryInterface
{
    private const string ALIAS = 'media';

    public function __construct(
        EntityManagerInterface $em,
        private readonly DomainEventPublisherInterface $eventPublisher,
    ) {
        parent::__construct($em, Media::class, self::ALIAS);
    }

    public function save(Media $media): void
    {
        $this->em->wrapInTransaction(function () use ($media): void {
            $this->em->persist($media);
            $this->em->flush();
            $this->eventPublisher->publishAll($media->releaseEvents());
        });
    }

    public function remove(Media $media): void
    {
        $this->em->wrapInTransaction(function () use ($media): void {
            $this->em->remove($media);
            $this->em->flush();
            $this->eventPublisher->publishAll($media->releaseEvents());
        });
    }

    public function findById(IdentifierVO $id): ?Media
    {
        return $this->em->find(Media::class, $id->value());
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        /** @var list<Media> $found */
        $found = $this->query()
            ->andWhere(self::ALIAS . '.id IN (:ids)')
            ->setParameter('ids', array_values(array_unique(array_map(static fn (IdentifierVO $id): string => $id->toRfc4122(), $ids))))
            ->getQuery()
            ->getResult();

        $byId = [];
        foreach ($found as $media) {
            $byId[(string) $media->id] = $media;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[(string) $id])) {
                $ordered[] = $byId[(string) $id];
            }
        }

        return $ordered;
    }
}
