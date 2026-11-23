<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Domain\ValueObject;

use AlexandreBulete\DddMediaBundle\Domain\Exception\UnsupportedFileTypeException;

/**
 * What a file is, read from its bytes, never from its name: the type a
 * browser announces can lie.
 *
 * Images only for now: the types every browser displays. Documents (PDF…)
 * come the day a project needs them.
 */
final readonly class FileFacts
{
    /** MIME type => the extension a stored file gets. */
    public const array ACCEPTED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/avif' => 'avif',
    ];

    public string $mimeType;

    /**
     * @param int<0, max>      $size   bytes
     * @param positive-int|null $width  pixels
     * @param positive-int|null $height pixels
     */
    public function __construct(
        string $mimeType,
        public int $size,
        public ?int $width = null,
        public ?int $height = null,
    ) {
        $mimeType = strtolower(trim($mimeType));
        if (!array_key_exists($mimeType, self::ACCEPTED_TYPES)) {
            throw new UnsupportedFileTypeException($mimeType);
        }
        $this->mimeType = $mimeType;
    }

    public function extension(): string
    {
        return self::ACCEPTED_TYPES[$this->mimeType];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }
}
