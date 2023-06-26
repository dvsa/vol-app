<?php

namespace Olcs\Form\Element;

use Interop\Container\ContainerInterface;
use Common\Service\Data\Search\Search as SearchDataService;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class SearchOrderFieldsetFactory
 */
class SearchOrderFieldsetFactory implements FactoryInterface
{
    protected $options;

    /**
     * Construct
     *
     * @param array $options Options
     *
     * @return $this
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return SearchOrderFieldset
     */
    public function createService(ServiceLocatorInterface $serviceLocator): SearchOrderFieldset
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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchOrderFieldset
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }

        $fs = new SearchOrderFieldset($this->options['name'], $this->options);
        $fs->setSearchService($container->get('DataServiceManager')->get(SearchDataService::class));
        return $fs;
    }
}
