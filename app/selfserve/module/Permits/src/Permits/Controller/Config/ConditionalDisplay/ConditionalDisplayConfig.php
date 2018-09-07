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

    const PERMIT_APP_CAN_MAKE_DECLARATION = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'canMakeDeclaration',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_BE_CANCELLED = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'canBeCancelled',
            'value' => true
        ],
    ];

    const PERMIT_APP_IS_CANCELLED = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'isCancelled',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_BE_WITHDRAWN = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'canBeWithdrawn',
            'value' => true
        ],
    ];

    const PERMIT_APP_IS_WITHDRAWN = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'isWithdrawn',
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
