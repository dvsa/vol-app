<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("prohibition-comment")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ProhibitionComment
{
    /**
     * @Form\Name("main")
     * @Form\Options({"label":"Prohibitions"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ProhibitionCommentMain")
     */
    public $main = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ResetFormActions")
     */
    public $formActions = null;
}
