<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("PermitsRequired")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class PermitsRequiredForm
{
    /**
     * @Form\Name("Fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\PermitsRequired")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
     */
    public $submitButton = null;
}
