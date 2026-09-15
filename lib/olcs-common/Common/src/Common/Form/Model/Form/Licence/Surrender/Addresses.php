<?php

namespace Common\Form\Model\Form\Licence\Surrender;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("surrender-addresses")
 * @Form\Type("Common\Form\Form")
 */
class Addresses
{
    /**
     * @Form\Name("correspondence")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\Correspondence")
     * @Form\Options({"label":"application_your-business_business-type.correspondence.label"})
     * @Form\Attributes({"id":"correspondenceAddress"})
     */
    public $correspondence;

    /**
     * @Form\Name("correspondence_address")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     */
    public $correspondenceAddress;

    /**
     * @Form\Name("contact")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\Contact")
     * @Form\Attributes({"id":"contactDetails"})
     */
    public $contact;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\SaveAndReturn")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
