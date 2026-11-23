<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Tests\Unit\Domain;

use AlexandreBulete\DddMediaBundle\Domain\Exception\UnsupportedFileTypeException;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\FileFacts;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileFactsTest extends TestCase
{
    #[Test]
    public function an_accepted_type_gets_its_extension(): void
    {
        $facts = new FileFacts(' IMAGE/JPEG ', 1200, 800, 600);

        self::assertSame('image/jpeg', $facts->mimeType);
        self::assertSame('jpg', $facts->extension());
        self::assertTrue($facts->isImage());
    }

    #[Test]
    public function any_other_type_is_refused(): void
    {
        $this->expectException(UnsupportedFileTypeException::class);

        new FileFacts('application/x-php', 10);
    }
}
