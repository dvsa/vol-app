<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 09/03/2015
 * Time: 12:06
 */

namespace Olcs\Form\Element;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SearchFilterFieldsetFactory implements FactoryInterface
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

        $fs = new SearchFilterFieldset($this->options['name'], $this->options);

        $fs->setSearchService($serviceLocator->get('DataServiceManager')->get('Olcs\Service\Data\Search\Search'));

        return $fs;
    }

}