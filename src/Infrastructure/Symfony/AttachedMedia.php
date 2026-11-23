<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Symfony;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddMediaBundle\Application\Command\RemoveMedia\RemoveMediaCommand;
use AlexandreBulete\DddMediaBundle\Application\Command\UploadMedia\UploadMediaCommand;
use AlexandreBulete\DddMediaBundle\Domain\Exception\MediaInUseException;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * A picture sent from another context's form (a cover, a visual): it joins
 * the library, and leaves it once that context lets it go.
 *
 * The owning context keeps the returned id, then calls release() on the one
 * it replaces, takes off, or drops along with its own record. Releasing never
 * fails: a media still shown elsewhere stays, one already gone is ignored.
 */
final readonly class AttachedMedia
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function attach(UploadedFile $file, ?string $alt = null): MediaId
    {
        $id = MediaId::generate();
        $this->commandBus->dispatch(new UploadMediaCommand(
            id: $id,
            localFile: $file->getPathname(),
            originalName: $file->getClientOriginalName(),
            alt: $alt,
        ));

        return $id;
    }

    /**
     * @return bool whether the media left the library
     */
    public function release(MediaId|string|\Stringable $id): bool
    {
        try {
            $this->commandBus->dispatch(new RemoveMediaCommand($id instanceof MediaId ? $id : MediaId::fromString((string) $id)));

            return true;
        } catch (MediaInUseException|EntityNotFoundException) {
            return false;
        }
    }
}
