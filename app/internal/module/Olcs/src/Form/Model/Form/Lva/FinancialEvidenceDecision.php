<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * Caseworker decision on the financial evidence, on the change document review page.
 *
 * Accept and reject post the AssessmentStatus values the API records; whether the evidence can
 * be accepted (every issue changed to a pass) is decided by the API, not here.
 *
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("financial-evidence-decision")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class FinancialEvidenceDecision
{
    /**
     * @Form\Type("Hidden")
     * @Form\Required(true)
     * @Form\Validator("Laminas\Validator\Digits")
     */
    public $analysisId = null;

    /**
     * @Form\Required(true)
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Select your decision",
     *      "label_attributes": {"class": "govuk-fieldset__legend--l"},
     *      "value_options":{
     *          "APPROVED":"Accept financial evidence",
     *          "REJECTED":"Reject financial evidence"
     *      }
     * })
     */
    public $decision = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "value": "decide",
     * })
     * @Form\Options({"label": "Save"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $saveDecision = null;
}

