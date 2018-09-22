<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("DeclineApplication")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class DeclineApplicationForm
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\DeclineApplicationFieldset")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\DeclineAccept")
     */
    public $declineButton = null;
}
