<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\Model;

use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;
use AlexandreBulete\DddMediaBundle\Domain\Event\MediaDescribed;
use AlexandreBulete\DddMediaBundle\Domain\Event\MediaRemoved;
use AlexandreBulete\DddMediaBundle\Domain\Event\MediaStored;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\FileFacts;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

/**
 * A file of the library: where it is stored, what it is, how to describe it
 * to whom cannot see it.
 *
 * The file itself never changes: a new version is a new media, so that a
 * resized copy cached anywhere can never show the old picture under the new
 * name. Other contexts point at a media by its id and nothing else.
 */
final class Media
{
    use RecordsEvents;

    public const int NAME_MAX_LENGTH = 255;
    public const int ALT_MAX_LENGTH = 255;

    private function __construct(
        public private(set) MediaId $id,
        public private(set) string $path,
        public private(set) string $originalName,
        public private(set) string $mimeType,
        public private(set) int $size,
        public private(set) ?int $width,
        public private(set) ?int $height,
        public private(set) ?string $alt,
        public private(set) \DateTimeImmutable $createdAt,
        public private(set) ?\DateTimeImmutable $updatedAt,
    ) {}

    /**
     * @param string $path where the storage keeps the file, as given to it
     */
    public static function store(MediaId $id, string $path, string $originalName, FileFacts $facts, ?string $alt, \DateTimeImmutable $at): self
    {
        if (trim($path) === '') {
            throw new \InvalidArgumentException('A media is stored somewhere.');
        }

        $media = new self(
            $id,
            $path,
            self::normalizeName($originalName, $facts),
            $facts->mimeType,
            $facts->size,
            $facts->width,
            $facts->height,
            self::normalizeAlt($alt),
            $at,
            null,
        );
        $media->recordEvent(new MediaStored((string) $id, $path, $facts->mimeType, $facts->size));

        return $media;
    }

    /**
     * The alternative text: what the picture shows, for a screen reader or a
     * picture that did not load. Empty for a purely decorative one.
     */
    public function describe(?string $alt, \DateTimeImmutable $at): void
    {
        $alt = self::normalizeAlt($alt);
        if ($alt === $this->alt) {
            return;
        }
        $this->alt = $alt;
        $this->updatedAt = $at;
        $this->recordEvent(new MediaDescribed((string) $this->id, $alt));
    }

    /**
     * Records the removal; the repository deletes, the use case drops the
     * file. Whether something still shows it is checked by the use case.
     */
    public function remove(): void
    {
        $this->recordEvent(new MediaRemoved((string) $this->id, $this->path));
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }

    private static function normalizeName(string $name, FileFacts $facts): string
    {
        $name = trim(basename(str_replace('\\', '/', $name)));
        if ($name === '') {
            $name = 'file.' . $facts->extension();
        }

        return mb_substr($name, 0, self::NAME_MAX_LENGTH);
    }

    private static function normalizeAlt(?string $alt): ?string
    {
        $alt = $alt === null ? null : trim($alt);
        if ($alt === null || $alt === '') {
            return null;
        }
        if (mb_strlen($alt) > self::ALT_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('An alternative text is %d characters at most.', self::ALT_MAX_LENGTH));
        }

        return $alt;
    }
}
