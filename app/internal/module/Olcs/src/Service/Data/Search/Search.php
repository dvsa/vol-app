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
    public function fetchResults()
    {
        $query = [
            'limit' => $this->getLimit(),
            'page' => $this->getPage()
        ];

        $uri = sprintf('/%s/%s?%s', $this->getSearch(), $this->getIndex(), http_build_query($query));
        return $this->getRestClient()->get($uri);
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
     * @return mixed
     */
    protected function getDataClass()
    {
        $manager = $this->getServiceLocator()->get('Olcs\Service\Data\Search\SearchTypeManager');
        return $manager->get($this->getIndex());
    }
}
