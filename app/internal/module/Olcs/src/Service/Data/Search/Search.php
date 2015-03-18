<?php

namespace Olcs\Service\Data\Search;

use Common\Service\Data\AbstractData;
use Common\Service\Data\Interfaces\ListData as ListDataInterface;
use Zend\Navigation\Service\ConstructedNavigationFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Http\Request as HttpRequest;

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
     * @TODO Remove the dependency on request. Set the object/data you need instead. e.g. $post or $post['filters']
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
     * @return array
     */
    public function fetchResults()
    {
        if (is_null($this->getData('results'))) {

            // Update selected values of filters FIRST.
            $this->updateFilterValuesFromForm();

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

            $this->setFilterOptionValues($data['Filters']);
            $this->populateFiltersFormOptions();
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
     * @return \Olcs\Data\Object\Search\SearchAbstract
     */
    protected function getDataClass()
    {
        $manager = $this->getServiceLocator()->get('Olcs\Service\Data\Search\SearchTypeManager');
        return $manager->get($this->getIndex());
    }

    /**
     * Updates selected values of the selected filters.
     *
     * @return null
     */
    public function updateFilterValuesFromForm()
    {
        $post = $this->getRequest()->getPost();

        foreach ($this->getFilters() as $filterClass) {

            /** @var \Olcs\Data\Object\Search\Filter\FilterAbstract $filterClass */
            if (isset($post['filter'][$filterClass->getKey()]) && !empty($post['filter'][$filterClass->getKey()])) {

                $filterClass->setValue($post['filter'][$filterClass->getKey()]);
            }
        }

        return;
    }

    /**
     * @return array
     */
    public function getFilterNames()
    {
        $output = [];

        foreach ($this->getFilters() as $filterClass) {

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
        foreach ($this->getFilters() as $filterClass) {

            /** @var $filterClass \Olcs\Data\Object\Search\Filter\FilterAbstract */
            if (isset($filterValues[$filterClass->getKey()])) {

                $filterClass->setOptions($filterValues[$filterClass->getKey()]);
            }

        }
    }
}
