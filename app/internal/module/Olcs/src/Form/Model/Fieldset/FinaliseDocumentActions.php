<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"actions-container"})
 * @Form\Name("form-actions")
 */
class FinaliseDocumentActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "Save"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary"})
     * @Form\Options({
     *     "label": "Back"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $back = null;

    /**
     * @Form\Attributes({"id":"cancel-finalise","type":"submit","class":"action--secondary"})
     * @Form\Options({
     *     "label": "Cancel"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancelFinalise = null;
}
