<?php

namespace Olcs\Form\Element;

use Common\Service\Data\Search\Search;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SearchFilterFieldsetFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SearchFilterFieldset
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : SearchFilterFieldset
    {
        $fs = new SearchFilterFieldset($options['name'], $options);

        $fs->setSearchService($container->get('DataServiceManager')->get(Search::class));
        return $fs;
    }
}
