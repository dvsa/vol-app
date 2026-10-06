<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * Caseworker review of one analysis on the financial evidence assessment page.
 *
 * Built through FormHelperService::createForm(), which adds the CSRF ("security") element.
 * One instance serves every tab: the view sets analysisId per tab before rendering it.
 *
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("financial-evidence-assessment-review")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class FinancialEvidenceAssessmentReview
{
    /**
     * @Form\Type("Hidden")
     * @Form\Required(true)
     * @Form\Validator("Laminas\Validator\Digits")
     */
    public $analysisId = null;

    /**
     * Posted as review=approve, so the action is explicit when more review actions are added.
     *
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "value": "approve",
     * })
     * @Form\Options({"label": "Approve document review"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $review = null;
}

