<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("CancelApplication")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class ChangeLicenceForm
{
    /**
     * @Form\Name("Fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\ChangeLicence")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\ChangeButton")
     */
    public $changeButton = null;
}
