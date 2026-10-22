<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * Caseworker change of one failed or skipped check to a pass, on the change document review page.
 *
 * Built through FormHelperService::createForm(), which adds the CSRF ("security") element.
 * One instance serves every issue: the view sets analysisId and row per issue before rendering it.
 * Whether the comment is required depends on the issue's own flag (a fail always needs a reason;
 * a skipped check does not), which the form cannot know, so that is enforced by the controller.
 *
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("financial-evidence-issue-override")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class FinancialEvidenceIssueOverride
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
     *          "haystack": \Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag::ROWS,
     *          "strict": true
     *     }
     * )
     */
    public $row = null;

    /**
     * @Form\Required(true)
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Change this recommendation to a pass?",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "N"})
     */
    public $changeRecommendation = null;

    /**
     * @Form\Type("TextArea")
     * @Form\Options({"label":"Reason for the change"})
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
     *     "value": "override",
     * })
     * @Form\Options({"label": "Save and continue"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $saveOverride = null;
}
