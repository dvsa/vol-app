<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("SpecialistHaulage")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class SpecialistHaulageForm
{
    /**
     * @Form\Name("Fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SectorList")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
     */
    public $submitButton = null;
}
