<?php

namespace Olcs\Form\Element;

use Interop\Container\ContainerInterface;
use Common\Service\Data\Search\Search;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class SearchOrderFieldsetFactory
 *
 * @package Olcs\Form\Element
 */
class SearchOrderFieldsetFactory implements FactoryInterface
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : SearchOrderFieldset
    {
        return $this->__invoke($serviceLocator, SearchOrderFieldset::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SearchOrderFieldset
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : SearchOrderFieldset
    {
        $serviceLocator = $container->getServiceLocator();
        $fs = new SearchOrderFieldset($this->options['name'], $this->options);
        $fs->setSearchService($serviceLocator->get('DataServiceManager')->get(Search::class));
        return $fs;
    }
}
