<?php

namespace Olcs\Form\Model\Form\Lva;

use Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag;
use Laminas\Form\Annotation;

/**
 * Caseworker change of one failed or skipped check to a pass, on the change document review page.
 *
 * One instance serves every issue: the view sets row per issue before rendering it.
 * Whether the comment is required depends on the issue's own flag (a fail always needs a reason;
 * a skipped check does not), which the form cannot know, so that is enforced by the controller.
 *
 * @codeCoverageIgnore
 */
#[Annotation\Name('financial-evidence-issue-override')]
#[Annotation\Attributes(['method' => 'post'])]
#[Annotation\Type('Common\Form\Form')]
#[Annotation\Options(['prefer_form_input_filter' => true])]
class FinancialEvidenceIssueOverride
{
    #[Annotation\Type('Hidden')]
    #[Annotation\Required(true)]
    #[Annotation\Validator('Laminas\Validator\Digits')]
    public $analysisId = null;

    #[Annotation\Type('Hidden')]
    #[Annotation\Required(true)]
    #[Annotation\Validator('Laminas\Validator\InArray', options: [
        'haystack' => OverrideDocumentAnalysisFlag::ROWS,
        'strict' => true,
    ])]
    public $row = null;

    #[Annotation\Required(true)]
    #[Annotation\Type('Radio')]
    #[Annotation\Options([
        'label' => 'Change this recommendation to a pass?',
        'value_options' => ['N' => 'No', 'Y' => 'Yes'],
        'fieldset-attributes' => ['class' => 'inline'],
    ])]
    #[Annotation\Attributes(['value' => 'N'])]
    public $changeRecommendation = null;

    #[Annotation\Type('TextArea')]
    #[Annotation\Options(['label' => 'Reason for the change'])]
    #[Annotation\Attributes(['class' => 'extra-long'])]
    #[Annotation\Filter('Laminas\Filter\StringTrim')]
    #[Annotation\Validator('Laminas\Validator\StringLength', options: ['max' => 1000])]
    public $comment = null;

    #[Annotation\Attributes([
        'data-module' => 'govuk-button',
        'type' => 'submit',
        'class' => 'govuk-button govuk-button--secondary',
        'value' => 'override',
    ])]
    #[Annotation\Options(['label' => 'Save and continue'])]
    #[Annotation\Type('\Common\Form\Elements\InputFilters\ActionButton')]
    public $saveOverride = null;
}
