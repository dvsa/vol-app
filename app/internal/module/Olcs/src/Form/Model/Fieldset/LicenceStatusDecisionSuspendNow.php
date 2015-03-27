<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 */
class LicenceStatusDecisionSuspendNow
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"suspend-now"})
     * @Form\Options({
     *     "label": "licence-status.suspension.now",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $suspendNow = null;

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
