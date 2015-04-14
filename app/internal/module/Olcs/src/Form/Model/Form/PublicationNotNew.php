<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("PublicationNotNew")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label":"Publication"})
 */
class PublicationNotNew
{
    /**
     * @Form\Name("readOnly")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\PublicationReadOnly")
     * @Form\Options({"readonly": true})
     */
    public $readOnly = null;

    /**
     * @Form\Name("readOnlyText")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Publication")
     * @Form\Options({"readonly": true})
     */
    public $readOnlyText = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelOnlyFormActions")
     */
    public $formActions = null;
}
