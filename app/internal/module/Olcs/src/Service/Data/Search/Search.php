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
     * Key value pairs for filters. Key is name of filter and value is value of filter (funily enough)
     *
     * @var array
     */
    protected $filters;

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
        //die('<pre>' . print_r($this->getFilterNames(), 1));

        if (is_null($this->getData('results'))) {
            $query = [
                'limit' => $this->getLimit(),
                'page' => $this->getPage(),
                'filters' => $this->getFilterNames()
            ];

            $uri = sprintf(
                '/%s/%s?%s',
                urlencode($this->getSearch()),
                $this->getDataClass()->getSearchIndices(),
                http_build_query($query)
            );

            $data = $this->getRestClient()->get($uri);

            $this->setData('results', $data);

            $this->setFilterValues($data['Filters']);

            //die('<pre>' . print_r($this->getFilters(), 1));
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

    public function fetchFiltersForm()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('SearchFilter');
        $form->remove('csrf');

        /** @var \Olcs\Data\Object\Search\Filter\FilterAbstract $filterClass */
        foreach ($this->getFilters() as $filterClass) {

            /** @var \Zend\Form\Element\Select $select */
            $select = $this->getServiceLocator()->get('FormElementManager')->get('Select');
            $select->setName($filterClass->getKey());
            $select->setLabel($filterClass->getTitle());

            //echo ('<pre>' . $filterClass->getTitle());

            $options = $this->formatFilterOptionsList(
                $filterClass->getOptionsKvp(),
                $filterClass->getOptionsKvp()
            );

            //echo('<pre>' . print_r($filterClass->getOptionsKvp(), 1));

            $select->setValueOptions($options);

            $form->add($select);
        }

        return $form;
    }

    /**
     * @param $keys
     * @param $values
     *
     * @return array
     */
    protected function formatFilterOptionsList($keys, $values)
    {
        return array_combine($keys, $values);
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
     * @return \Olcs\Data\Object\Search\SearchAbstract
     */
    protected function getDataClass()
    {
        $manager = $this->getServiceLocator()->get('Olcs\Service\Data\Search\SearchTypeManager');
        return $manager->get($this->getIndex());
    }

    /**
     * @return array
     */
    public function getFilterNames()
    {
        $output = [];

        foreach ($this->getFilters() as $filterClass) {

            /** @var $filterClass \Olcs\Data\Object\Search\Filter\FilterAbstract */
            $output[$filterClass->getKey()] = $filterClass->getValue();
        }

        return $output;
    }

    /**
     * Returns an array of filters relevant to this index.
     *
     * @return mixed
     */
    public function getFilters()
    {
        return $this->getDataClass()->getFilters();
    }

    /**
     * Sets the available filter values into the filters.
     *
     * @param array $filters
     */
    public function setFilterValues(array $filterValues)
    {
        foreach ($this->getFilters() as $filter) {

            /** @var $filter \Olcs\Data\Object\Search\Filter\FilterAbstract */
            if (isset($filterValues[$filter->getKey()])) {

                $filter->setOptions($filterValues[$filter->getKey()]);
            }

        }
    }
}
