<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddDoctrineBridge\Type\GuidType;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;

final class MediaIdType extends GuidType
{
    public const string NAME = 'media_id';

    protected string $name = self::NAME;

    protected string $voClass = MediaId::class;
}
