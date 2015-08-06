<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("document-relink")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class DocumentRelink
{
    /**
     * @Form\Name("document-relink-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\DocumentRelinkDetails")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\DocumentRelinkActions")
     */
    public $formActions = null;
}
