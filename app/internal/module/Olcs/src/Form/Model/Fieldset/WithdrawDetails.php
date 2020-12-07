<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("withdraw-details")
 * @Form\Options({"label":""})
 */
class WithdrawDetails
{
    /**
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Withdraw reason",
     *      "value_options":{
     *          \Common\RefData::WITHDRAWN_REASON_WITHDRAWN: "Withdrawn",
     *          \Common\RefData::WITHDRAWN_REASON_REG_IN_ERROR: "Registered in error"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"checkbox inline"
     *      },
     *      "disable_inarray_validator": true,
     * })
     * @Form\Validator({
     *      "name": "Laminas\Validator\NotEmpty",
     *      "options": {
     *          "messages": {
     *              Laminas\Validator\NotEmpty::IS_EMPTY: "Please select a reason for withdrawing the application"
     *          }
     *      }
     * })
     */
    public $reason;
}
