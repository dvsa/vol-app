<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("Withdraw")
 * @Form\Options({"label":""})
 * @Form\Attributes({"method":"post"})
 */
class IrhpApplicationWithdraw
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Name("withdraw-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\WithdrawIrhp")
     */
    public $details;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\WithdrawFormActions")
     */
    public $formActions = null;
}
