<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("details")
 */
class RefundFeeDetails
{
    /**
     * Customer reference
     *
     * @Form\Options({
     *      "short-label":"Customer reference",
     *      "label":"Customer reference",
     *      "label_attributes": {"id": "label-customer-reference"}
     * })
     * @Form\Required(true)
     * @Form\Type("Text")
     */

    public $customerReference = null;

    /**
     * Customer name
     *
     * @Form\Options({
     *      "short-label":"Customer name",
     *      "label":"Customer name",
     *      "label_attributes": {"id": "label-customer-name"}
     * })
     * @Form\Required(true)
     * @Form\Type("Text")
     */
    public $customerName = null;
}
