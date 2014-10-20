<?php

namespace Olcs\Service\Data\Search;

use Common\Service\Data\AbstractData;
use Zend\Navigation\Service\ConstructedNavigationFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class Search
 * @package Olcs\Service\Data\Search
 */
class Search extends AbstractData implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var string
     */
    protected $index;
    /**
     * @var array
     */
    protected $params;

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return \Zend\Navigation\Navigation
     */
    public function getNavigation()
    {
        $manager = $this->getServiceLocator()->get('Olcs\Service\Data\Search\SearchTypeManager');

        $services = $manager->getRegisteredServices();
        foreach(array_merge($services['factories'], $services['invokableClasses']) as $searchIndexName) {
            $searchIndex = $manager->get($searchIndexName);
            $nav[] = $searchIndex->getNavigation();
        }

        $navFactory = new ConstructedNavigationFactory($nav);

        return $navFactory->createService($this->getServiceLocator()->getServiceLocator());
    }

    /**
     * @return array
     */
    public function fetchIndexes()
    {
        return [
            'licence' => 'Licence',
            'application' => 'Application',
            'cases' => 'Cases',
            'busregs' => 'Bus Registrations',
            'addresses' => 'Addresses',
            'people' => 'People',
            'historicaltms' => 'Historical TMs',
            'vehicles' => 'Vehicles'
        ];
    }

    /**
     * @return array
     */
    public function fetchResults()
    {
        return [];
    }

    /**
     * @return mixed
     */
    public function fetchResultsTable()
    {
        $tableBuilder = $this->getServiceLocator()->get('Table');

        return $tableBuilder->buildTable(
            $this->getDataClass()->getTableConfig(),
            $this->fetchResults(),
            [],
            false
        );
    }

    /**
     * @return mixed
     */
    protected function getDataClass()
    {
        $manager = $this->getServiceLocator()->get('Olcs\Service\Data\Search\SearchTypeManager');
        return $manager->get($this->getIndex());
    }
}
