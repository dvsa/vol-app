<?php

namespace Common\Form\Model\Form\Licence;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lic-people")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class AddPerson
{
    /**
     * @Form\Attributes({"class":""})
     * @Form\ComposedObject("Common\Form\Model\Form\Licence\Fieldset\Person")
     */
    public $data;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
