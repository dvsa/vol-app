<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 09/03/2015
 * Time: 12:06
 */

namespace Olcs\Form\Element;

use Interop\Container\ContainerInterface;
use Common\Service\Data\Search\Search;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class SearchFilterFieldsetFactory
 *
 * @package Olcs\Form\Element
 */
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
    public function createService(ServiceLocatorInterface $serviceLocator) : SearchFilterFieldset
    {
        return $this->__invoke($serviceLocator, SearchFilterFieldset::class);
    }

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
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }

        $fs = new SearchFilterFieldset($this->options['name'], $this->options);

        $fs->setSearchService($container->get('DataServiceManager')->get(Search::class));
        return $fs;
    }
}
