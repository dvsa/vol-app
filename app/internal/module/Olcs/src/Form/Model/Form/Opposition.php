<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Opposition")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Opposition
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OppositionFields")
     */
    public $fields = null;

    /**
     * @Form\Name("person")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OpposerPerson")
     */
    public $person = null;

    /**
     * @Form\Name("contact")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OpposerContact")
     */
    public $contact = null;

    /**
     * @Form\Name("address")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\OpposerAddress")
     */
    public $address = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
