<?php
namespace Permits\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("Qa")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\QaForm")
 */
class QaForm
{
    /**
     * @Form\Name("qa")
     * @Form\Type("Laminas\Form\Fieldset")
     */
    public $fieldset = null;
}
