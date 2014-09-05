<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("conviction-comment")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class ConvictionComment
{
    /**
     * @Form\Name("main")
     * @Form\Options({"label":"Convictions"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ConvictionCommentMain")
     */
    public $main = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ResetFormActions")
     */
    public $formActions = null;
}
