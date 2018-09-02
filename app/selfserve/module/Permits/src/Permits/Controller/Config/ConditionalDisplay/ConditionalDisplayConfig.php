<?php

namespace Permits\Controller\Config\ConditionalDisplay;

use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;

/**
 * Holds conditional display configs that are used regularly
 */
class ConditionalDisplayConfig
{
    const PERMIT_APP_NOT_SUBMITTED =  [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_CHECK_ANSWERS = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'canCheckAnswers',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_BE_SUBMITTED = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'canBeSubmitted',
            'value' => true
        ],
    ];

    const PERMIT_APP_UNDER_CONSIDERATION = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'isUnderConsideration',
            'value' => true
        ],
    ];
}
