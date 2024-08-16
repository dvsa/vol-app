<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("details")
 */
class ReverseTransactionDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Reason"})
     * @Form\Type("\Common\Form\Elements\InputFilters\Textarea")
     */
    public $reason = null;

    /**
     * Customer reference, required for misc payments
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
