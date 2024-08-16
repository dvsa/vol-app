<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-register-service")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class BusRegisterService
{
    /**
     * @Form\Options({"label":"Timetable route","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\BusRegisterServiceGrant")
     */
    public $grant = null;

    /**
     * @Form\Options({"label":"Timetable route","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\BusRegisterServiceTimetable")
     */
    public $timetable = null;

    /**
     * @Form\Options({"label":"Conditions","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\BusRegisterServiceConditions")
     */
    public $conditions = null;

    /**
     * @Form\Name("fields")
     * @Form\Options({"label":"Register service","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\BusRegisterService")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
