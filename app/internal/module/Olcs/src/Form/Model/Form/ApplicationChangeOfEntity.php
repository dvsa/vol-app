<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("application-change-of-entity")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class ApplicationChangeOfEntity
{
    /**
     * @Form\Name("change-details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ApplicationChangeOfEntityDetails")
     */
    public $details = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ApplicationChangeOfEntityFormActions")
     */
    public $formActions = null;
}
