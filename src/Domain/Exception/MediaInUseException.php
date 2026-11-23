<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\Exception;

/**
 * A file something still shows (a cover, a visual): deleting it would leave a
 * hole there. Replace it where it is used first.
 */
final class MediaInUseException extends \DomainException
{
    public readonly string $usages;

    /**
     * @param list<string> $usages what uses it, as its owner words it
     */
    public function __construct(
        public readonly string $name,
        array $usages,
    ) {
        $this->usages = implode(', ', $usages);
        parent::__construct(sprintf('"%s" is still used: %s.', $name, $this->usages));
    }
}
