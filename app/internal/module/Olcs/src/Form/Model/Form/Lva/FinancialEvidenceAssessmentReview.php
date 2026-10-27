<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation;

/**
 * Caseworker review of one analysis on the financial evidence assessment page.
 *
 * Built through FormHelperService::createForm(), which adds the CSRF ("security") element.
 * One instance serves every tab: the view sets analysisId per tab before rendering it.
 *
 * @codeCoverageIgnore
 */
#[Annotation\Name('financial-evidence-assessment-review')]
#[Annotation\Attributes(['method' => 'post'])]
#[Annotation\Type('Common\Form\Form')]
#[Annotation\Options(['prefer_form_input_filter' => true])]
class FinancialEvidenceAssessmentReview
{
    #[Annotation\Type('Hidden')]
    #[Annotation\Required(true)]
    #[Annotation\Validator('Laminas\Validator\Digits')]
    public $analysisId = null;

    /** Posted as review=accept, so the action is explicit when more review actions are added. */
    #[Annotation\Attributes([
        'data-module' => 'govuk-button',
        'type' => 'submit',
        'class' => 'govuk-button',
        'value' => 'accept',
    ])]
    #[Annotation\Options(['label' => 'Accept document review'])]
    #[Annotation\Type('\Common\Form\Elements\InputFilters\ActionButton')]
    public $review = null;
}
