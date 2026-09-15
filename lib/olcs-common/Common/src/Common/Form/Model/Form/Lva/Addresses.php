<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-addresses")
 * @Form\Type("Common\Form\Form")
 */
class Addresses
{
    /**
     * @Form\Name("correspondence")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\Correspondence")
     * @Form\Options({
     *     "label":"application_your-business_business-type.correspondence.label",
     *     "hint": "application_correspondence-address.hint"
     * })
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
     */
    public $contact;

    /**
     * @Form\Name("establishment")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\Establishment")
     */
    public $establishment;

    /**
     * @Form\Name("establishment_address")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\AddressOptional")
     * @Form\Options({
     *     "label":"application_your-business_business-type.establishment.label",
     *     "hint": "application_your-business_business-type.establishment.hint"
     *  })
     */
    public $establishmentAddress;

    /**
     * @Form\Name("consultant")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\TransportConsultant")
     * @Form\Options({"label":"application_your-business_business-type.transport-consultant.label"})
     */
    public $transportConsultant;

    /**
     * @Form\Name("consultantAddress")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     */
    public $transportConsultantAddress;

    /**
     * @Form\Name("consultantContact")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\ContactOptional")
     */
    public $transportConsultantContact;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
