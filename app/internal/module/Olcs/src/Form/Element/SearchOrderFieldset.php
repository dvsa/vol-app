<?php

namespace Olcs\Form\Element;

use Common\Service\Data\Search\SearchAwareTrait as SearchAwareTrait;
use Zend\Form\Element\Select;
use Zend\Form\Fieldset;

/**
 * Class SearchOrderFieldset
 */
class SearchOrderFieldset extends Fieldset
{
    use SearchAwareTrait;

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        $index = $this->getOption('index');
        $this->getSearchService()->setIndex($index);

        $orderOptions = $this->getSearchService()->getOrderOptions();

        if (!empty($orderOptions)) {
            /** @var \Zend\Form\Element\Select $select */
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
