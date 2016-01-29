<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class ConfirmFormActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.continue",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $confirm = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"cancel"})
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.cancel",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
