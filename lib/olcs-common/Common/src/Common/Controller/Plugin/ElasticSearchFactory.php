<?php

namespace Common\Controller\Plugin;

use Common\Service\Data\Search\Search;
use Common\Service\Data\Search\SearchType;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ElasticSearchFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ElasticSearch
    {
        $plugin = new ElasticSearch();

        $searchService = $container->get('DataServiceManager')->get(Search::class);
        $searchTypeService = $container->get('DataServiceManager')->get(SearchType::class);
        $navigation = $container->get('navigation');

        $plugin->setSearchService($searchService);
        $plugin->setSearchTypeService($searchTypeService);
        $plugin->setNavigationService($navigation);

        return $plugin;
    }
}
