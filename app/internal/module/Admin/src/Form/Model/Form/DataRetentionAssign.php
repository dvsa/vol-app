<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("AssignItem")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class DataRetentionAssign
{
    /**
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\DataRetentionAssignFields")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
