<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddMediaBundle\Application\Query\FindMediaLibrary\FindMediaLibraryQuery;
use AlexandreBulete\DddMediaBundle\Domain\Model\Media;
use AlexandreBulete\DddMediaBundle\Domain\Repository\MediaRepositoryInterface;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResource;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResourceFactory;
use AlexandreBulete\DddSyliusBundle\Grid\GridPageResolver;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

/**
 * Feeds the grid through the query bus; pagination already happened in the
 * repository, hence FixedAdapter.
 */
final readonly class MediaGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private MediaResourceFactory $factory,
    ) {}

    /**
     * @return PagerfantaInterface<MediaResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        /** @var array<string, mixed> $criteria */
        $criteria = $parameters->get('criteria', []);
        /** @var array<string, string> $sorting */
        $sorting = $parameters->get('sorting', $grid->getSorting());

        /** @var MediaRepositoryInterface $models */
        $models = $this->queryBus->ask(new FindMediaLibraryQuery(
            page: GridPageResolver::getCurrentPage($grid, $parameters),
            itemsPerPage: GridPageResolver::getItemsPerPage($grid, $parameters),
            criteria: $criteria,
            withSorting: $sorting,
        ));

        $data = [];
        foreach ($models as $model) {
            /** @var Media $model */
            $data[] = $this->factory->fromModel($model);
        }

        return new Pagerfanta(new FixedAdapter($models->count(), $data));
    }
}
