<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"POST"})
 * @Form\Type("Common\Form\Form")
 */
class AcceptAndPayForm
{
    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\AcceptAndPay")
     */
    public $submit = null;
}
