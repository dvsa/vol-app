<?php

namespace Olcs\Form\Element;

use Interop\Container\ContainerInterface;
use Common\Service\Data\Search\Search;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SearchFilterFieldsetFactory implements FactoryInterface
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SearchFilterFieldset
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchFilterFieldset
    {
        $fs = new SearchFilterFieldset($this->options['name'], $this->options);
        $fs->setSearchService($container->get('DataServiceManager')->get(Search::class));
        return $fs;
    }
}
