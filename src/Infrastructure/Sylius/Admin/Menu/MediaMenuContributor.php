<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Admin\Menu;

use AlexandreBulete\DddSyliusBundle\Admin\Menu\MenuContributorInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * One "Media" entry, shown to whom may browse the library.
 */
#[AutoconfigureTag('app.menu_contributor', ['priority' => 20])]
final readonly class MediaMenuContributor implements MenuContributorInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorization,
    ) {}

    public function contribute(ItemInterface $menu): void
    {
        if (!$this->authorization->isGranted('media.find_media_library')) {
            return;
        }

        $menu->addChild('media', ['route' => 'media_admin_media_index'])
            ->setLabel('media.menu.root')
            ->setLabelAttribute('icon', 'tabler:photo');
    }
}
