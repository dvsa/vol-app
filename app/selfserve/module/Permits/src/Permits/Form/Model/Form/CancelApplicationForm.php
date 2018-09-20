<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("CancelApplication")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class CancelApplicationForm
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\CancelApplication")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\CancelButton")
     */
    public $cancelButton = null;
}
