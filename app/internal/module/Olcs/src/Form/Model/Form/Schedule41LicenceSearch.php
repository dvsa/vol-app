<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("schedule41licencesearch")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Schedule41LicenceSearch
{
    /**
     * @Form\Name("licence-number")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\Schedule41LicenceSearchLicenceNumber")
     */
    public $licenceNumber = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ConfirmFormActions")
     */
    public $formActions = null;
}
