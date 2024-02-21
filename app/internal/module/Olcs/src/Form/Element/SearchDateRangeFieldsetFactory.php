<?php

namespace Olcs\Form\Element;

use Psr\Container\ContainerInterface;
use Common\Service\Data\Search\Search;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
