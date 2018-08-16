<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("CancelApplication")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class WithdrawApplicationForm
{
    /**
     * @Form\Name("Fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\WithdrawApplication")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\WithdrawButton")
     */
    public $withdrawButton = null;
}
