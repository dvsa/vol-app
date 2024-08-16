<?php

/**
 * Created by PhpStorm.
 * User: craig
 * Date: 09/03/2015
 * Time: 12:08
 */

namespace Olcs\Form\Element;

use Common\Form\Elements\Custom\DateSelect;
use Laminas\Form\Fieldset;
use Common\Service\Data\Search\SearchAwareTrait as SearchAwareTrait;

/**
 * Class SearchDateRangeFieldset
 *
 * @package Olcs\Form\Element
 */
class SearchDateRangeFieldset extends Fieldset
{
    use SearchAwareTrait;

    /**
     * initial function fired when fieldset called
     *
     * @return void
     */
    public function init()
    {
        $index = $this->getOption('index');
        $this->getSearchService()->setIndex($index);
        /** @var \Common\Data\Object\Search\Aggregations\DateRange\DateRangeAbstract $class */
        foreach ($this->getSearchService()->getDateRanges() as $class) {
            $date = new DateSelect();
            $date->setName($class->getKey());
            $date->setLabel($class->getTitle());
            $date->setShouldCreateEmptyOption(true);
            $this->add($date);
        }
    }
}
