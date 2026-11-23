<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResource;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Field\TwigField;
use Sylius\Bundle\GridBundle\Builder\Filter\StringFilter;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

class MediaGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    /**
     * @param list<int> $limits
     */
    public function __construct(
        private readonly array $limits,
    ) {}

    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(MediaGridProvider::class)
            ->setLimits($this->limits)
            ->orderBy('createdAt', 'desc')
            ->addFilter(StringFilter::create('originalName')->setLabel('media.search'))
            ->addField(TwigField::create('thumbnailUrl', '@DddMedia/admin/thumbnail.html.twig')->setLabel('media.preview'))
            ->addField(StringField::create('originalName')->setLabel('media.original_name')->setSortable(true))
            ->addField(StringField::create('alt')->setLabel('media.alt'))
            ->addField(StringField::create('dimensions')->setLabel('media.dimensions'))
            ->addField(StringField::create('sizeLabel')->setLabel('media.size'))
            ->addField(DateTimeField::create('createdAt')->setLabel('media.created_at')->setSortable(true))
            ->addActionGroup(MainActionGroup::create(CreateAction::create()))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create(), DeleteAction::create()))
        ;
    }

    public function getResourceClass(): string
    {
        return MediaResource::class;
    }
}
