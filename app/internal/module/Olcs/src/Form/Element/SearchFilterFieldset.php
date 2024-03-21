<?php

/**
 * Created by PhpStorm.
 * User: craig
 * Date: 09/03/2015
 * Time: 12:08
 */

namespace Olcs\Form\Element;

use Laminas\Form\Fieldset;
use Laminas\Form\Element\Select;
use Common\Service\Data\Search\Search as SearchService;
use Common\Service\Data\Search\SearchAwareTrait as SearchAwareTrait;

/**
 * Class SearchFilterFieldset
 *
 * @package Olcs\Form\Element
 */
class SearchFilterFieldset extends Fieldset
{
    use SearchAwareTrait;

    public function init()
    {
        $index = $this->getOption('index');
        $this->getSearchService()->setIndex($index);

        /** @var \Common\Data\Object\Search\Aggregations\Terms\TermsAbstract $filterClass */
        foreach ($this->getSearchService()->getFilters() as $filterClass) {

            /** @var \Laminas\Form\Element\Select $select */
            $select = new Select();
            $select->setName($filterClass->getKey());
            $select->setLabel($filterClass->getTitle());
            $select->setEmptyOption('All');
            $select->setDisableInArrayValidator(true);

            $this->add($select);
        }
    }
}
