<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\Service;

use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

/**
 * Implemented by whatever shows media (a book's cover, a post's visual): the
 * library asks every implementation before deleting a file. Tagged
 * `media.usage` by autoconfiguration.
 *
 * Nothing else ties a media to its users: no foreign key, no back-reference.
 */
interface MediaUsageInterface
{
    /**
     * What uses this media, worded for the person deleting it ("the cover of
     * …"); empty when nothing does.
     *
     * @return list<string>
     */
    public function usagesOf(MediaId $id): array;
}
