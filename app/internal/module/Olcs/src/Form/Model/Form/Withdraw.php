<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Withdraw")
 * @Form\Options({"label":""})
 * @Form\Attributes({"method":"post"})
 */
class Withdraw
{

    /**
     * @Form\Name("withdraw-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\WithdrawDetails")
     */
    public $details;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ConfirmFormActions")
     */
    public $formActions = null;
}
