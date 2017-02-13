<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class LicenceStatusDecisionFormActions
{

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"affect-immediate"})
     * @Form\Options({
     *     "label": "Affect now"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $affectImmediate = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"submit"})
     * @Form\Options({
     *     "label": "Save"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"cancel"})
     * @Form\Options({
     *     "label": "Cancel"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"remove"})
     * @Form\Options({
     *     "label": "licence-status.remove"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $remove = null;
}
