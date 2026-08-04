<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-taxi-phv")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class TaxiPhv
{
    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\TableRequired")
     */
    public $table;

    /**
     * @Form\Name("dataTrafficArea")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TrafficArea")
     */
    public $dataTrafficArea;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
