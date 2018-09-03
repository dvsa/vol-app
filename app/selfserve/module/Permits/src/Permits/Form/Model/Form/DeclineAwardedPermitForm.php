<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("DeclineAwardedPermit")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class DeclineAwardedPermitForm
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\DeclinedPermitFieldset")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SubmitAccept")
     */
    public $declineButton = null;
}
