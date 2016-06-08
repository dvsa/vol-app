<?php

namespace Olcs\Form\Element;

use Common\Service\Data\Search\Search;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $fs = new SearchOrderFieldset($this->options['name'], $this->options);

        $fs->setSearchService($serviceLocator->get('DataServiceManager')->get(Search::class));

        return $fs;
    }
}
