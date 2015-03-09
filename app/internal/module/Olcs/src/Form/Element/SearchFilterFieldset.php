<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 09/03/2015
 * Time: 12:08
 */

namespace Olcs\Form\Element;

use Zend\Form\Fieldset;
use Zend\Form\Element\Select;
use \Olcs\Service\Data\Search\Search as SearchService;

class SearchFilterFieldset extends Fieldset
{
    protected $searchService;

    /**
     * @return SearchService
     */
    public function getSearchService()
    {
        return $this->searchService;
    }

    /**
     * @param SearchService $searchService
     */
    public function setSearchService(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function init()
    {
        $index = $this->getOption('index');
        $this->getSearchService()->setIndex($index);

        foreach ($this->getSearchService()->getFilters() as $filterClass) {

            /** @var \Zend\Form\Element\Select $select */
            $select = new Select;
            $select->setName($filterClass->getKey());
            $select->setLabel($filterClass->getTitle());
            $select->setEmptyOption('');

            $this->add($select);
        }
    }


}