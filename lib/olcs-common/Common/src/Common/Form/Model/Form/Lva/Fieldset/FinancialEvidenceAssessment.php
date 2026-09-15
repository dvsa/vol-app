<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("assessment")
 */
class FinancialEvidenceAssessment
{
    /**
     * @Form\Type("Laminas\Form\Element\Radio")
     * @Form\Options({
     *     "label": "lva-financial-evidence-assessment-outcome.label",
     *     "value_options": {
     *         "satisfactory": "lva-financial-evidence-assessment-outcome.satisfactory",
     *         "unsatisfactory": "lva-financial-evidence-assessment-outcome.unsatisfactory"
     *     }
     * })
     * @Form\Attributes({"id":"assessment_outcome"})
     * @Form\Required(true)
     * @Form\Validator("NotEmpty", options={"messages": {"isEmpty": "lva-financial-evidence-assessment-outcome.required"}})
     */
    public $outcome;

    /**
     * @Form\Type("Textarea")
     * @Form\Attributes({"id":"assessment_comment"})
     * @Form\Options({
     *     "label": "lva-financial-evidence-assessment-comment.label"
     * })
     */
    public $comment;
}

