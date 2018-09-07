<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("AcceptAndPay")
 * @Form\Attributes({"method":"POST"})
 * @Form\Type("Common\Form\Form")
 */

class AcceptAndPayForm
{
    /**
     * @Form\Name("AcceptAndPay")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\AcceptAndPay")
     */
    public $submitButton = null;
}