<?php

namespace Olcs\Service\Data\Search;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;
use Zend\Navigation\Service\ConstructedNavigationFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class Search
 * @package Olcs\Service\Data\Search
 */
class Search extends AbstractData implements ServiceLocatorAwareInterface, ListDataInterface
{
    use ServiceLocatorAwareTrait;

    protected $serviceName = 'Search';

    /**
     * @var string
     */
    protected $index;

    /**
     * @var array
     */
    protected $search;

    /**
     * @var \ArrayObject
     */
    protected $query;

    /**
     * @param mixed $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param \ArrayObject $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return \ArrayObject
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function getLimit()
    {
        return ($this->getQuery() === null || empty($this->getQuery()->limit))? 10 : $this->getQuery()->limit;
    }

    public function getPage()
    {
        return ($this->getQuery() === null || empty($this->getQuery()->page))? 1 : $this->getQuery()->page;
    }

    /**
     * @return \Zend\Navigation\Navigation
     */
    public function getNavigation()
    {
        foreach ($this->getSearchTypes() as $searchIndex) {
            $nav[] = $searchIndex->getNavigation();
        }

        $navFactory = $this->getServiceLocator()->getServiceLocator()->get('NavigationFactory');

        return $navFactory->getNavigation($nav);
    }

    /**
     * @return array
     */
    public function fetchResults()
    {
        if (is_null($this->getData('results'))) {
            $query = [
                'limit' => $this->getLimit(),
                'page' => $this->getPage()
            ];

            $uri = sprintf(
                '/%s/%s?%s',
                urlencode($this->getSearch()),
                $this->getDataClass()->getSearchIndices(),
                http_build_query($query)
            );

            $this->setData('results', $this->getRestClient()->get($uri));
        }
        return $this->getData('results');
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
            [
                'query' => $this->getQuery(),
                'limit' => $this->getLimit(),
                'page' => $this->getPage()
            ],
            false
        );
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

    protected function getSearchTypes()
    {
        $manager = $this->getServiceLocator()->get('Olcs\Service\Data\Search\SearchTypeManager');
        $services = $manager->getRegisteredServices();

        $indexes = [];

        foreach (array_merge($services['factories'], $services['invokableClasses']) as $searchIndexName) {
            $indexes[] = $manager->get($searchIndexName);
        }

        return $indexes;
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
