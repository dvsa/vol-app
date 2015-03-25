<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class LicenceStatusDecisionMessagesFormActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"continue"})
     * @Form\Options({
     *     "label": "Continue",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $continue = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"cancel"})
     * @Form\Options({
     *     "label": "Cancel",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
