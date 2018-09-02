<?php

namespace Permits\Controller\Config\Form;

/**
 * Holds conditional display configs that are used regularly - eventually it'd be nice to have ::class in here
 * but we're limited by the quality of our current form building code
 */
class FormConfig
{
    const FORM_CHECK_ANSWERS = [
        'checkAnswers' => [
            'formClass' => 'CheckAnswersForm',
        ],
    ];

    const FORM_FEE = [
        'fee' => [
            'formClass' => 'feesForm',
        ],
    ];
}
