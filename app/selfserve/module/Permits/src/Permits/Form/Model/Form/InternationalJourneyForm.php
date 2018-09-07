<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("InternationalJourney")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class InternationalJourneyForm
{
    /**
     * @Form\Name("Fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\InternationalJourney")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
     */
    public $submitButton = null;

}
