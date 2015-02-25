<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Create variation fieldset
 */
class CreateVariation
{
    /**
     * @Form\Type("DateSelect")
     * @Form\Options({
     *     "label": "create-variation-application-received-date"
     * })
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $receivedDate;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *     "label": "create-variation-fee-required",
     *     "value_options":{
     *         "Y":"Yes",
     *         "N":"No"
     *     },
     *     "fieldset-attributes" : {
     *         "class":"inline"
     *     }
     * })
     */
    public $feeRequired;
}
