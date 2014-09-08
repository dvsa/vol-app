<?php

namespace Olcs\View\Helper;

/**
 * Class TableFilters
 */
class TableFilters extends AbstractWidget
{
    public function toString()
    {
        $vh = $this->getServiceLocator()->get('form');
        $form = $this->getContainer()->getValue();

        if ($form instanceof \Zend\Form\Form) {
            return $vh->render($form);
        }

        return '';
    }
}
