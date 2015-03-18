<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("withdraw-details")
 * @Form\Options({"label":""})
 */
class WithdrawDetails extends Base
{
    /**
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Withdraw reason",
     *      "value_options":{
     *          "withdrawn": "Withdrawn",
     *          "reg_in_error": "Registered in error"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"checkbox inline"
     *      },
     *      "disable_inarray_validator": true,
     * })
     * @Form\Validator({
     *      "name": "Zend\Validator\NotEmpty",
     *      "options": {
     *          "messages": {
     *              Zend\Validator\NotEmpty::IS_EMPTY: "Please select a reason for withdrawing the application"
     *          }
     *      }
     * })
     */
    public $reason;
}
