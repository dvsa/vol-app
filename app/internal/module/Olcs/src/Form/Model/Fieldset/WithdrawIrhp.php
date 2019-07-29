<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("withdraw-details")
 * @Form\Options({"label":""})
 */
class WithdrawIrhp
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Withdraw Reason",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "permit_app_withdraw_reason"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $reason = null;
}
