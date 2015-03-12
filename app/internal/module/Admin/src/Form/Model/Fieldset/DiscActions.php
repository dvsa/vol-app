<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"actions-container"})
 * @Form\Name("fee-actions")
 */
class DiscActions
{

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary", "id": "submit"})
     * @Form\Options({
     *     "label": "Print discs",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $print = null;
}
