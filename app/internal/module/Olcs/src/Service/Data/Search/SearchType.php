<?php

namespace Olcs\Service\Data\Search;
use Common\Service\Data\Interfaces\ListData as ListDataInterface;
use Common\Service\Data\Search\SearchTypeManager;
use Zend\Navigation\Service\AbstractNavigationFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SearchType
 * @package Olcs\Service\Data\Search
 */
class SearchType implements ListDataInterface, FactoryInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $searchTypeManager;

    /**
     * @var AbstractNavigationFactory
     */
    protected $navigationFactory;

    /**
     * @return mixed
     */
    public function getSearchTypeManager()
    {
        return $this->searchTypeManager;
    }

    /**
     * @param mixed $searchTypeManager
     */
    public function setSearchTypeManager($searchTypeManager)
    {
        $this->searchTypeManager = $searchTypeManager;
    }

    /**
     * @return mixed
     */
    public function getNavigationFactory()
    {
        return $this->navigationFactory;
    }

    /**
     * @param mixed $navigationFactory
     */
    public function setNavigationFactory($navigationFactory)
    {
        $this->navigationFactory = $navigationFactory;
    }

    /**
     * Fetch back a set of options for a drop down list, context passed is parameters which may need to be passed to the
     * back end to filter the result set returned, use groups when specified should, cause this method to return the
     * data as a multi dimensioned array suitable for display in opt-groups. It is permissible for the method to ignore
     * this flag if the data doesn't allow for option groups to be constructed.
     *
     * @param mixed $context
     * @param bool $useGroups
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $options = [];

        foreach ($this->getSearchTypes() as $searchIndex) {
            /** @var $searchIndex \Olcs\Data\Object\Search\SearchAbstract  */
            $options[$searchIndex->getKey()] = $searchIndex->getTitle();
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function getSearchTypes()
    {
        $services = $this->getSearchTypeManager()->getRegisteredServices();

        $indexes = [];

        foreach (array_merge($services['factories'], $services['invokableClasses']) as $searchIndexName) {
            $indexes[] = $this->getSearchTypeManager()->get($searchIndexName);
        }

        return $indexes;
    }

    /**
     * @return \Zend\Navigation\Navigation
     */
    public function getNavigation()
    {
        $nav = [];
        foreach ($this->getSearchTypes() as $searchIndex) {
            /** @var \Olcs\Data\Object\Search\SearchAbstract $searchIndex */
            $nav[] = $searchIndex->getNavigation();
        }

        return $this->getNavigationFactory()->getNavigation($nav);
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->setNavigationFactory($serviceLocator->get('NavigationFactory'));
        $this->setSearchTypeManager($serviceLocator->get(SearchTypeManager::class));

        return $this;
    }
}
