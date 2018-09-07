<?php

namespace Permits\Controller\Config\Form;

use Permits\Controller\Config\DataSource\PermitApplication as PermitApplicationDataSource;

/**
 * Holds conditional display configs that are used regularly - eventually it'd be nice to have ::class in here
 * but we're limited by the quality of our current form building code
 */
class FormConfig
{
    const FORM_CANCEL_PERMIT_APP_KEY = 'cancelPermitApp';
    const FORM_WITHDRAW_PERMIT_APP_KEY = 'withdrawPermitApp';

    const FORM_EMISSIONS = [
        'emissions' => [
            'formClass' => 'Euro6EmissionsForm',
            'dataSource' => PermitApplicationDataSource::DATA_KEY,
        ],
    ];

    const FORM_CABOTAGE = [
        'cabotage' => [
            'formClass' => 'CabotageForm',
            'dataSource' => PermitApplicationDataSource::DATA_KEY,
        ],
    ];

    const FORM_DECLARATION = [
        'cabotage' => [
            'formClass' => 'DeclarationForm',
            'dataSource' => PermitApplicationDataSource::DATA_KEY,
        ],
    ];

    const FORM_CHECK_ANSWERS = [
        'checkAnswers' => [
            'formClass' => 'CheckAnswersForm',
        ],
    ];

    const FORM_FEE = [
        'fee' => [
            'formClass' => 'FeesForm',
        ],
    ];

    const FORM_CANCEL_PERMIT_APP = [
        self::FORM_CANCEL_PERMIT_APP_KEY => [
            'formClass' => 'CancelApplicationForm',
        ],
    ];

    const FORM_WITHDRAW_PERMIT_APP = [
        self::FORM_CANCEL_PERMIT_APP_KEY => [
            'formClass' => 'WithdrawApplicationForm',
        ],
    ];

    const FORM_ACCEPT_AND_PAY = [
        'acceptAndPay' => [
            'formClass' => 'AcceptAndPayForm',
        ],
    ];
}
