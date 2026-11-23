<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Grid\MediaGrid;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\State\Processor\CreateMediaProcessor;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\State\Processor\DeleteMediaProcessor;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\State\Processor\UpdateMediaProcessor;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\State\Provider\MediaItemProvider;
use AlexandreBulete\DddMediaBundle\Infrastructure\Symfony\Form\Type\MediaType;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Validator\Constraints as Assert;

#[AsResource(
    alias: 'media.media',
    section: 'admin',
    formType: MediaType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    name: 'media',
    pluralName: 'media',
    driver: false,
    operations: [
        new Create(processor: CreateMediaProcessor::class, validationContext: ['groups' => ['Default', 'create']]),
        new Update(provider: MediaItemProvider::class, processor: UpdateMediaProcessor::class),
        new Delete(provider: MediaItemProvider::class, processor: DeleteMediaProcessor::class),
        new Index(grid: MediaGrid::class),
    ],
)]
final class MediaResource implements ResourceInterface
{
    public function __construct(
        public ?AbstractUid $id = null,
        // The file itself, on creation only: a media's file never changes.
        #[Assert\NotNull(groups: ['create'])]
        public ?UploadedFile $file = null,
        #[Assert\Length(max: Media::ALT_MAX_LENGTH)]
        public ?string $alt = null,
        // Read-only, for the grid and the edit page.
        public ?string $originalName = null,
        public ?string $mimeType = null,
        public ?string $sizeLabel = null,
        public ?string $dimensions = null,
        public ?string $thumbnailUrl = null,
        public ?\DateTimeImmutable $createdAt = null,
    ) {}

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    public static function fromModel(Media $media, ?string $thumbnailUrl = null): self
    {
        return new self(
            id: $media->id->value(),
            alt: $media->alt,
            originalName: $media->originalName,
            mimeType: $media->mimeType,
            sizeLabel: self::bytes($media->size),
            dimensions: $media->width !== null && $media->height !== null ? $media->width . ' × ' . $media->height : null,
            thumbnailUrl: $thumbnailUrl,
            createdAt: $media->createdAt,
        );
    }

    private static function bytes(int $size): string
    {
        return match (true) {
            $size >= 1_048_576 => number_format($size / 1_048_576, 1, ',', ' ') . ' Mo',
            $size >= 1024 => number_format($size / 1024, 0, ',', ' ') . ' ko',
            default => $size . ' o',
        };
    }
}
