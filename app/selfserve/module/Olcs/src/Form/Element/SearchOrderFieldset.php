<?php

namespace Olcs\Form\Element;

use Laminas\Form\Element\Radio;
use Laminas\Form\Fieldset;
use Laminas\Form\Element\Select;
use Common\Service\Data\Search\Search as SearchService;
use Common\Service\Data\Search\SearchAwareTrait as SearchAwareTrait;

/**
 * Class SearchOrderFieldset
 *
 * @package Olcs\Form\Element
 */
class SearchOrderFieldset extends Fieldset
{
    use SearchAwareTrait;

    public function init()
    {
        $index = $this->getOption('index');
        $this->getSearchService()->setIndex($index);

        /** @var \Common\Data\Object\Search\Aggregations\Terms\TermsAbstract $filterClass */
        $orderOptions = $this->getSearchService()->getOrderOptions();

        if (!empty($orderOptions)) {
            /** @var \Laminas\Form\Element\Select $select */
            $select = new Select();
            $select->setName('order');
            $select->setLabel('Order by');
            $select->setEmptyOption('Best match');

            $options = [];

            foreach ($orderOptions as $option) {
                $options[$option['field'] . '-' . $option['order']] = $option['field_label'];
            }

            $select->setValueOptions($options);

            $this->add($select);
        }
    }
}
