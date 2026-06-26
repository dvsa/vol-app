<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-taxi-phv-licence")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class TaxiPhvLicence
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TaxiPhvLicenceData")
     * @Form\Options({"label":"application_taxi-phv_licence-sub-action.data"})
     */
    public $data;

    /**
     * @Form\Name("contactDetails")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TaxiPhvContactDetails")
     * @Form\Options({"label":"application_taxi-phv_licence-sub-action.contactDetails"})
     */
    public $contactDetails;

    /**
     * @Form\Name("address")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     */
    public $address;

    /**
     * @Form\Name("trafficArea")
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $trafficArea;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
