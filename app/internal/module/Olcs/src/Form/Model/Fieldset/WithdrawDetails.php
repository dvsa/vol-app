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
     * @Form\Required(true)
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Withdraw reason",
     *      "value_options":{
     *          "withdrawn": "Withdrawn",
     *          "reg_in_error": "Registered in error"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"checkbox inline"
     *      }
     * })
     */
    public $reason;
}
