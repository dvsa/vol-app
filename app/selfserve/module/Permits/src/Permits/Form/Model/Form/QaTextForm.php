<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("QaText")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class QaTextForm
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\QaText")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
     */
    public $submitButton = null;
}
