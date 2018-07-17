<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("Declaration")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */

class DeclarationForm
{
    /**
     * @Form\Name("Fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Declaration")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SubmitAccept")
     */
    public $submitAcceptButton = null;
}
