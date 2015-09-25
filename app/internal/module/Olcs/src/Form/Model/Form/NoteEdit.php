<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("note")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class NoteEdit
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":"Add note"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\NoteEdit")
     */
    public $fields = null;

        /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
