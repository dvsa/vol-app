<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class UpdateContinuationFormActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"continue-licence"})
     * @Form\Options({
     *     "label": "Continue Licence"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $continueLicence = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"submit"})
     * @Form\Options({
     *     "label": "save"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"cancel"})
     * @Form\Options({
     *     "label": "cancel"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"print-separator"})
     * @Form\Options({
     *     "label": "Print separator"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $printSeperator = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"view-continuation","target":"_blank"})
     * @Form\Options({
     *     "label": "View digital continuation"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionLink")
     */
    public $viewContinuation = null;
}
