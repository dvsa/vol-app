<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Cabotage")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Permits\Form\Form")
 */
class CabotageForm
{
    /**
     * @Form\Name("CabotageFields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Cabotage")
     */
    public $meetsEuro6 = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
     */
    public $submitButton = null;
}
