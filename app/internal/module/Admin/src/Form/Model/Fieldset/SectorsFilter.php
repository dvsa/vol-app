<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
//use Zend\Form\View\Helper\FormSelect;
use Common\Form\View\Helper\Readonly\FormSelect;

/**
 * @Form\Type("Zend\Form\Fieldset")
 */
class SectorsFilter
{
    /**
     * @Form\Attributes({"id":"sector-list","placeholder":""})
     * @Form\Options({
     *     "label": "Sector",
     *     "selected": "01"
     * })
     * @Form\Type("FormSelect")
     */
    public $date = null;
}
