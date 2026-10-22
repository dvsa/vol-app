<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * Caseworker change of one passed check to a fail, on the change document review page. The
 * reverse of FinancialEvidenceIssueOverride.
 *
 * Built through FormHelperService::createForm(), which adds the CSRF ("security") element.
 * One instance serves every pass: the view sets analysisId and row per pass before rendering it.
 * Every row this form targets is a genuine pass, so a reason is always required when changing it
 * to a fail; that is enforced by the controller, which also allows an optional note when not.
 *
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("financial-evidence-pass-override")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class FinancialEvidencePassOverride
{
    /**
     * @Form\Type("Hidden")
     * @Form\Required(true)
     * @Form\Validator("Laminas\Validator\Digits")
     */
    public $analysisId = null;

    /**
     * @Form\Type("Hidden")
     * @Form\Required(true)
     * @Form\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": \Dvsa\Olcs\Transfer\Command\Document\OverridePassedDocumentAnalysisFlag::ROWS,
     *          "strict": true
     *     }
     * )
     */
    public $row = null;

    /**
     * @Form\Required(true)
     * @Form\Type("Radio")
     * @Form\Attributes({"radios_wrapper_attributes": {"class": "govuk-radios--standard"}, "value": "Y"})
     * @Form\Options({
     *      "label": "Do you want to change the outcome of this issue?",
     *      "value_options":{
     *          "Y":"Yes",
     *          "N":"No"
     *      }
     * })
     */
    public $changeRecommendation = null;

    /**
     * @Form\Type("TextArea")
     * @Form\Options({"label":"Can you provide more detail?"})
     * @Form\Attributes({"class":"extra-long"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":1000})
     */
    public $comment = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class":"govuk-button govuk-button--secondary",
     *     "value": "override-fail",
     * })
     * @Form\Options({"label": "Save and continue"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $savePassOverride = null;
}


