<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class IrfoPsvAuthFormActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({
     *     "label": "Save"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"grant"})
     * @Form\Options({
     *     "label": "Grant"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $grant = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"approve"})
     * @Form\Options({
     *     "label": "Approve"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $approve = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"generate"})
     * @Form\Options({
     *     "label": "Generate"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $generate = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"cns"})
     * @Form\Options({
     *     "label": "CNS"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cns = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"withdraw"})
     * @Form\Options({
     *     "label": "Withdraw"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $withdraw = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"refuse"})
     * @Form\Options({
     *     "label": "Refuse"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $refuse = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"cancel"})
     * @Form\Options({
     *     "label": "Cancel"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
