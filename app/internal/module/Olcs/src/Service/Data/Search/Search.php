<?php

namespace Olcs\Service\Data\Search;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;
use Zend\Navigation\Service\ConstructedNavigationFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Http\Request as HttpRequest;

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
     * @var HttpRequest
     */
    protected $request;

    /**
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param HttpRequest $request
     */
    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;
        return $this;
    }

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

    /**
     * @return int
     */
    public function getLimit()
    {
        return ($this->getQuery() === null || empty($this->getQuery()->limit))? 10 : $this->getQuery()->limit;
    }

    /**
     * @return int
     */
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

            //die('<pre>' . print_r($uri, 1));

            $data = $this->getRestClient()->get($uri);

            //die('<pre>' . print_r($data, 1));

            $this->setData('results', $data);

            $this->setFilterOptionValues($data['Filters']);
            $this->populateFiltersFormOptions();

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

        //die(get_class($this->getQuery()));

        if ($this->getRequest()->getPost()) {
            foreach ($this->getRequest()->getPost() as $param => $value) {
                $this->getQuery()->set($param, $value);
            }
        }

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

    public function fetchFiltersFormObject()
    {
        $form = $this->getServiceLocator()
             ->get('ViewHelperManager')
             ->get('placeholder')
             ->getContainer('searchFilter')
             ->getValue();

        return $form;
    }

    public function populateFiltersFormOptions()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->fetchFiltersFormObject();

        /** @var \Olcs\Data\Object\Search\Filter\FilterAbstract $filterClass */
        foreach ($this->getFilters() as $filterClass) {

            $options = array_combine(
                $filterClass->getOptionsKvp(),
                $filterClass->getOptionsKvp()
            );

            $select = $form->get('filter')->get($filterClass->getKey());
            $select->setValueOptions($options);
            $select->setValue($filterClass->getValue());
        }

        return $form;
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

        $post = $this->getRequest()->getPost();

        //die('<pre>' . print_r($post, 1));

        foreach ($this->getFilters() as $filterClass) {

            /** @var \Olcs\Data\Object\Search\Filter\FilterAbstract $filterClass */
            if (isset($post['filter'][$filterClass->getKey()]) && !empty($post['filter'][$filterClass->getKey()])) {

                $filterClass->setValue($post['filter'][$filterClass->getKey()]);
            }

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
    public function setFilterOptionValues(array $filterValues)
    {
        foreach ($this->getFilters() as $filter) {

            /** @var $filter \Olcs\Data\Object\Search\Filter\FilterAbstract */
            if (isset($filterValues[$filter->getKey()])) {

                $filter->setOptions($filterValues[$filter->getKey()]);
            }

        }
    }
}
