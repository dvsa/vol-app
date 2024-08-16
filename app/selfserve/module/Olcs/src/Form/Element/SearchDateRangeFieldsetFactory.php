<?php

namespace Olcs\Form\Element;

use Common\Service\Data\Search\Search;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SearchDateRangeFieldsetFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SearchDateRangeFieldset
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchDateRangeFieldset
    {
        $fs = new SearchDateRangeFieldset($options['name'], $options);
        $fs->setSearchService($container->get('DataServiceManager')->get(Search::class));
        return $fs;
    }
}
