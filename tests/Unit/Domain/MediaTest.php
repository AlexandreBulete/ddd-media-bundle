<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Tests\Unit\Domain;

use AlexandreBulete\DddMediaBundle\Domain\Event\MediaDescribed;
use AlexandreBulete\DddMediaBundle\Domain\Event\MediaRemoved;
use AlexandreBulete\DddMediaBundle\Domain\Event\MediaStored;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\FileFacts;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MediaTest extends TestCase
{
    #[Test]
    public function a_stored_media_keeps_the_name_without_its_folders(): void
    {
        $media = $this->media('C:\\Users\\moi\\Photos\\couverture.png', '  ');

        self::assertSame('couverture.png', $media->originalName);
        self::assertNull($media->alt);
        self::assertSame([3, 2], [$media->width, $media->height]);
        self::assertInstanceOf(MediaStored::class, $media->releaseEvents()[0]);
    }

    #[Test]
    public function a_nameless_upload_is_named_after_its_type(): void
    {
        self::assertSame('file.png', $this->media('', null)->originalName);
    }

    #[Test]
    public function describing_records_only_a_change(): void
    {
        $media = $this->media('a.png', 'Une couverture');
        $media->releaseEvents();

        $media->describe(' Une couverture ', new \DateTimeImmutable());
        self::assertSame([], $media->releaseEvents());
        self::assertNull($media->updatedAt);

        $media->describe('Une autre', new \DateTimeImmutable());
        self::assertInstanceOf(MediaDescribed::class, $media->releaseEvents()[0]);
        self::assertSame('Une autre', $media->alt);
    }

    #[Test]
    public function an_alternative_text_has_a_limit(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->media('a.png', str_repeat('x', Media::ALT_MAX_LENGTH + 1));
    }

    #[Test]
    public function removing_says_which_file_to_drop(): void
    {
        $media = $this->media('a.png', null);
        $media->releaseEvents();
        $media->remove();

        $event = $media->releaseEvents()[0];
        self::assertInstanceOf(MediaRemoved::class, $event);
        self::assertSame('2026/11/x.png', $event->path);
    }

    private function media(string $name, ?string $alt): Media
    {
        return Media::store(MediaId::generate(), '2026/11/x.png', $name, new FileFacts('image/png', 70, 3, 2), $alt, new \DateTimeImmutable());
    }
}
