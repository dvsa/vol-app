<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("continuation-detail-filter")
 * @Form\Attributes({"method":"get","class":"filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ChecklistReminderFilter
{
    /**
     * @Form\Name("filters")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\ChecklistReminderFilter")
     */
    public $filters = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","data-container-class":"js-hidden"})
     * @Form\Options({
     *     "label": "filter-button"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;
}
