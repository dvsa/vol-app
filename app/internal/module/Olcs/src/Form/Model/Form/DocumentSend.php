<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("document-send")
 * @Form\Attributes({
 *     "method":"post",
 * })
 * @Form\Type("Common\Form\Form")
 */
class DocumentSend
{
    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\DocumentSendActions")
     */
    public $formActions = null;
}
