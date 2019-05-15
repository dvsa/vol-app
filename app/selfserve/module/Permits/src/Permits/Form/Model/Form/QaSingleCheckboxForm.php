<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("QaSingleCheckbox")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class QaSingleCheckboxForm
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\QaSingleCheckbox")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
     */
    public $submitButton = null;
}
