<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 09/03/2015
 * Time: 12:08
 */

namespace Olcs\Form\Element;

use Zend\Form\Fieldset;
use Zend\Form\Element\DateSelect;
use Common\Service\Data\Search\Search as SearchService;

/**
 * Class SearchDateRangeFieldset
 *
 * @package Olcs\Form\Element
 */
class SearchDateRangeFieldset extends Fieldset
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

        /** @var \Olcs\Data\Object\Search\Aggregations\DateRange\DateRangeAbstract $class */
        foreach ($this->getSearchService()->getDateRanges() as $class) {

            //die($class->getTitle());

            /** @var \Zend\Form\Element\DateSelect $select */
            $date = new DateSelect;
            $date->setName($class->getKey());
            $date->setLabel($class->getTitle());
            $date->setShouldCreateEmptyOption(true);

            $this->add($date);
        }
    }
}
