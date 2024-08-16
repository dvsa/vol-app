<?php

namespace Olcs\Form\Element;

use Psr\Container\ContainerInterface;
use Common\Service\Data\Search\Search as SearchDataService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SearchOrderFieldsetFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SearchOrderFieldset
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchOrderFieldset
    {
        $fs = new SearchOrderFieldset($options['name'], $options);
        $fs->setSearchService($container->get('DataServiceManager')->get(SearchDataService::class));
        return $fs;
    }
}
