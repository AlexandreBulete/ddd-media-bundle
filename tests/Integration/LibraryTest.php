<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Tests\Integration;

use AlexandreBulete\DddMediaBundle\Application\Command\DescribeMedia\DescribeMediaCommand;
use AlexandreBulete\DddMediaBundle\Application\Command\DescribeMedia\DescribeMediaHandler;
use AlexandreBulete\DddMediaBundle\Application\Command\RemoveMedia\RemoveMediaCommand;
use AlexandreBulete\DddMediaBundle\Application\Command\RemoveMedia\RemoveMediaHandler;
use AlexandreBulete\DddMediaBundle\Application\Command\UploadMedia\UploadMediaCommand;
use AlexandreBulete\DddMediaBundle\Application\Command\UploadMedia\UploadMediaHandler;
use AlexandreBulete\DddMediaBundle\Application\Query\FindMediaByIds\FindMediaByIdsHandler;
use AlexandreBulete\DddMediaBundle\Application\Query\FindMediaByIds\FindMediaByIdsQuery;
use AlexandreBulete\DddMediaBundle\Domain\Exception\MediaInUseException;
use AlexandreBulete\DddMediaBundle\Domain\Exception\UnsupportedFileTypeException;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;
use AlexandreBulete\DddMediaBundle\Infrastructure\Doctrine\DoctrineMediaRepository;
use AlexandreBulete\DddMediaBundle\Infrastructure\Storage\FlysystemFileStorage;
use AlexandreBulete\DddMediaBundle\Infrastructure\Storage\NativeFileInspector;
use AlexandreBulete\DddMediaBundle\Tests\Integration\Fixture\FixedUsage;
use AlexandreBulete\DddMediaBundle\Tests\Integration\Fixture\NullEventPublisher;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/**
 * The use cases against a real database and a real (in-memory) storage.
 */
final class LibraryTest extends TestCase
{
    private const string PICTURE = __DIR__ . '/../Fixture/files/red-3x2.png';

    private EntityManagerInterface $em;
    private DoctrineMediaRepository $repository;
    private Filesystem $files;
    private FixedUsage $usage;
    private MockClock $clock;

    protected function setUp(): void
    {
        $this->em = MediaDatabase::migrated();
        $this->repository = new DoctrineMediaRepository($this->em, new NullEventPublisher());
        $this->files = new Filesystem(new InMemoryFilesystemAdapter());
        $this->usage = new FixedUsage();
        $this->clock = new MockClock('2026-11-23 10:00:00');
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }

    #[Test]
    public function an_upload_is_stored_by_month_under_its_id(): void
    {
        $media = $this->upload('Couverture.PNG', 'Une couverture rouge');
        $this->em->clear();

        $found = $this->repository->findById($media->id);
        self::assertInstanceOf(Media::class, $found);
        self::assertSame(sprintf('2026/11/%s.png', strtolower((string) $media->id)), $found->path);
        self::assertSame(['image/png', 3, 2, 'Une couverture rouge'], [$found->mimeType, $found->width, $found->height, $found->alt]);
        self::assertSame(filesize(self::PICTURE), $found->size);
        self::assertTrue($this->files->fileExists($found->path));
    }

    #[Test]
    public function a_file_of_another_type_is_refused_and_nothing_is_kept(): void
    {
        try {
            $this->uploadFile(__DIR__ . '/../Fixture/files/notes.txt', 'notes.png');
            self::fail('A text file passed for a picture.');
        } catch (UnsupportedFileTypeException $e) {
            self::assertSame('text/plain', $e->mimeType);
        }

        self::assertSame([], $this->files->listContents('', true)->toArray());
        self::assertCount(0, $this->repository);
    }

    #[Test]
    public function a_media_in_use_cannot_be_removed(): void
    {
        $media = $this->upload('a.png');
        $this->usage->usages[(string) $media->id] = ['la couverture de « Un livre »'];

        try {
            $this->remove($media->id);
            self::fail('A media in use was removed.');
        } catch (MediaInUseException $e) {
            self::assertSame('la couverture de « Un livre »', $e->usages);
        }

        self::assertTrue($this->files->fileExists($media->path));
    }

    #[Test]
    public function removing_drops_the_row_and_the_file(): void
    {
        $media = $this->upload('a.png');

        $this->remove($media->id);
        $this->em->clear();

        self::assertNull($this->repository->findById($media->id));
        self::assertFalse($this->files->fileExists($media->path));
    }

    #[Test]
    public function describing_changes_only_the_alternative_text(): void
    {
        $media = $this->upload('a.png', 'Avant');

        (new DescribeMediaHandler($this->repository, $this->clock))(new DescribeMediaCommand($media->id, 'Après'));
        $this->em->clear();

        self::assertSame('Après', $this->repository->findById($media->id)?->alt);
    }

    #[Test]
    public function a_page_gets_its_media_in_the_order_it_asked(): void
    {
        $first = $this->upload('1.png');
        $second = $this->upload('2.png');
        $this->em->clear();

        $found = (new FindMediaByIdsHandler($this->repository))(new FindMediaByIdsQuery([$second->id, MediaId::generate(), $first->id, $second->id]));

        self::assertSame(['2.png', '1.png', '2.png'], array_map(static fn (Media $m): string => $m->originalName, $found));
    }

    private function upload(string $name, ?string $alt = null): Media
    {
        return $this->uploadFile(self::PICTURE, $name, $alt);
    }

    private function uploadFile(string $file, string $name, ?string $alt = null): Media
    {
        $handler = new UploadMediaHandler($this->repository, new NativeFileInspector(), new FlysystemFileStorage($this->files), $this->clock);

        return $handler(new UploadMediaCommand(MediaId::generate(), $file, $name, $alt));
    }

    private function remove(MediaId $id): void
    {
        (new RemoveMediaHandler($this->repository, new FlysystemFileStorage($this->files), [$this->usage]))(new RemoveMediaCommand($id));
    }
}
