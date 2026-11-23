<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Tests\Unit\Infrastructure;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddMediaBundle\Application\Command\RemoveMedia\RemoveMediaCommand;
use AlexandreBulete\DddMediaBundle\Application\Command\UploadMedia\UploadMediaCommand;
use AlexandreBulete\DddMediaBundle\Domain\Exception\MediaInUseException;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;
use AlexandreBulete\DddMediaBundle\Infrastructure\Symfony\AttachedMedia;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AttachedMediaTest extends TestCase
{
    #[Test]
    public function an_attached_file_is_uploaded_under_the_id_it_returns(): void
    {
        $bus = new RecordingBus();
        $file = new UploadedFile(__DIR__ . '/../../Fixture/files/red-3x2.png', 'Front Cover.png', 'image/png', null, true);

        $id = (new AttachedMedia($bus))->attach($file, 'A red square');

        $command = $bus->dispatched[0];
        self::assertInstanceOf(UploadMediaCommand::class, $command);
        self::assertTrue($id->equals($command->id));
        self::assertSame('Front Cover.png', $command->originalName);
        self::assertSame('A red square', $command->alt);
    }

    #[Test]
    public function releasing_removes_the_media(): void
    {
        $bus = new RecordingBus();
        $id = MediaId::generate();

        self::assertTrue((new AttachedMedia($bus))->release((string) $id));
        self::assertInstanceOf(RemoveMediaCommand::class, $bus->dispatched[0]);
        self::assertTrue($id->equals($bus->dispatched[0]->id));
    }

    #[Test]
    public function releasing_a_media_in_use_or_gone_keeps_quiet(): void
    {
        $attached = new AttachedMedia(new RecordingBus(new MediaInUseException('cover.png', ['a page'])));
        self::assertFalse($attached->release(MediaId::generate()));

        $attached = new AttachedMedia(new RecordingBus(new EntityNotFoundException(Media::class, MediaId::generate())));
        self::assertFalse($attached->release(MediaId::generate()));
    }
}

final class RecordingBus implements CommandBusInterface
{
    /** @var list<CommandInterface<mixed>> */
    public array $dispatched = [];

    public function __construct(
        private readonly ?\Throwable $failure = null,
    ) {}

    public function dispatch(CommandInterface $command): mixed
    {
        $this->dispatched[] = $command;
        if ($this->failure !== null) {
            throw $this->failure;
        }

        // Nothing reads the result here: the two commands return no value.
        return null; // @phpstan-ignore return.type
    }
}
