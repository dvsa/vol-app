<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 09/03/2015
 * Time: 12:06
 */

namespace Olcs\Form\Element;

use Common\Service\Data\Search\Search;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SearchDateRangeFieldsetFactory
 *
 * @package Olcs\Form\Element
 */
class SearchDateRangeFieldsetFactory implements FactoryInterface
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

        $fs = new SearchDateRangeFieldset($this->options['name'], $this->options);

        $fs->setSearchService($serviceLocator->get('DataServiceManager')->get(Search::class));

        return $fs;
    }
}
