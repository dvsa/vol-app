<?php

namespace Olcs\Form\Element;

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
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $fs = new SearchOrderFieldset($this->options['name'], $this->options);

        $fs->setSearchService($serviceLocator->get('DataServiceManager')->get(SearchDataService::class));

        return $fs;
    }
}
