<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("CheckAnswers")
 * @Form\Attributes({"method":"POST"})
 * @Form\Type("Common\Form\Form")
 */

class CheckAnswersForm
{
    /**
     * @Form\Name("Fields")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\CheckAnswers")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\SubmitAccept")
     */
    public $submitAcceptButton = null;
}
