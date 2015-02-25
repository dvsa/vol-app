<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("note")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "label":"Note"})
 */
class Note
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Type("Hidden")
     */
    public $noteType = null;

    /**
     * @Form\Type("Hidden")
     */
    public $transportManager = null;

    /**
     * @Form\Name("main")
     * @Form\Options({"label":"Add note"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\NoteMain")
     */
    public $main = null;

        /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
