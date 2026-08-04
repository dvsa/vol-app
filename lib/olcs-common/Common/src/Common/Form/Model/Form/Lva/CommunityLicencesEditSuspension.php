<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-community-licences-stop")
 * @Form\Attributes({"method":"post", "class":"table__form"})
 * @Form\Type("Common\Form\Form")
 */
class CommunityLicencesEditSuspension
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\CommunityLicencesEditSuspension")
     */
    public $data;

    /**
     * @Form\Attributes({"id":"dates"})
     * @Form\Name("dates")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\CommunityLicencesEditSuspensionDates")
     */
    public $dates;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\OkCancelActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
