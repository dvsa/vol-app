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
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\PublicationNotNew")
     * @Form\Options({"readonly": true})
     */
    public $readOnlyText = null;
}
