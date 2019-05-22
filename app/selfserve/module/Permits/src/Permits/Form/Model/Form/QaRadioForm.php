<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("QaRadio")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class QaRadioForm
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\QaRadio")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
     */
    public $submitButton = null;
}
