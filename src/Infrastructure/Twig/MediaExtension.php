<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Twig;

use AlexandreBulete\DddMediaBundle\Infrastructure\Imagine\MediaUrls;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 *     {% set cover = media(book.coverId) %}
 *     {% if cover %}<img src="{{ media_url(cover, 'cover') }}" alt="{{ cover.alt }}">{% endif %}
 */
final class MediaExtension extends AbstractExtension
{
    public function __construct(
        private readonly MediaUrls $urls,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('media', $this->urls->media(...)),
            new TwigFunction('media_url', $this->urls->url(...)),
        ];
    }
}
