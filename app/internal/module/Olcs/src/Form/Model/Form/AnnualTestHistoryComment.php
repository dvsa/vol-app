<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("annual-test-history-comment")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\InputFilter("Common\Form\InputFilter")
 */
class AnnualTestHistoryComment
{
    /**
     * @Form\Name("main")
     * @Form\Options({"label":"Annual test history"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Main")
     */
    public $main = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ResetFormActions")
     */
    public $formActions = null;
}
