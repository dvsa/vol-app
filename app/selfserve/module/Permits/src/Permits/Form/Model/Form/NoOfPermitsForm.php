<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("NoOfPermits")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class NoOfPermitsForm
{
    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
     * @Form\Flags({"priority": -1})
     */
    public $submitButton = null;
}
