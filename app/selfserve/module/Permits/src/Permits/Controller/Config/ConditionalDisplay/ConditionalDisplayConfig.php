<?php

namespace Permits\Controller\Config\ConditionalDisplay;

use Permits\Controller\Config\DataSource\LicencesAvailable;
use Permits\Controller\Config\DataSource\AvailableTypes;
use Permits\Controller\Config\DataSource\OpenWindows;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\View\Helper\EcmtSection;
use Permits\View\Helper\IrhpApplicationSection;

/**
 * Holds conditional display configs that are used regularly
 */
class ConditionalDisplayConfig
{
    const PERMIT_APP_CAN_APPLY = [
        AvailableTypes::DATA_KEY => [
            'view' => [
                'template' => 'permits/window-closed',
            ]
        ],
    ];

    const PERMIT_APP_CAN_APPLY_SINGLE = [
        OpenWindows::DATA_KEY => [
            'view' => [
                'template' => 'permits/window-closed',
            ]
        ],
        LicencesAvailable::DATA_KEY => [
            'view' => [
                'template' => 'permits/not-eligible',
            ],
            'key' => 'hasAvailableEcmtLicences',
            'value' => true
        ]
    ];

    const PERMIT_APP_NOT_SUBMITTED =  [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
    ];

    const IRHP_APP_NOT_SUBMITTED =  [
        IrhpAppDataSource::DATA_KEY => [
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
    ];

    const IRHP_APP_READY_FOR_COUNTRIES =  [
        IrhpAppDataSource::DATA_KEY => [
            'key' => 'canUpdateCountries',
            'value' => true
        ],
    ];

    const IRHP_APP_READY_FOR_NO_OF_PERMITS =  [
        IrhpAppDataSource::DATA_KEY => [
            'key' => 'isReadyForNoOfPermits',
            'value' => true
        ],
    ];

    const PERMIT_APP_CONFIRM_CHANGE_LICENCE = [
        IrhpAppDataSource::DATA_KEY => [
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
        LicencesAvailable::DATA_KEY => [
            'key' => 'hasAvailableLicences',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ]
    ];

    const PERMIT_APP_CONFIRM_CHANGE_LICENCE_ECMT = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
        LicencesAvailable::DATA_KEY => [
            'key' => 'hasAvailableLicences',
            'value' => true,
            'route' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
        ]
    ];

    const PERMIT_APP_CAN_CHECK_ANSWERS = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'canCheckAnswers',
            'value' => true,
            'route' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];

    const PERMIT_APP_CAN_MAKE_DECLARATION = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'canMakeDeclaration',
            'value' => true
        ],
    ];

    const IRHP_APP_CAN_BE_CANCELLED = [
        IrhpAppDataSource::DATA_KEY => [
            'key' => 'canBeCancelled',
            'value' => true
        ],
    ];

    const IRHP_APP_IS_CANCELLED = [
        IrhpAppDataSource::DATA_KEY => [
            'key' => 'isCancelled',
            'value' => true
        ],
    ];

    const IRHP_APP_CAN_MAKE_DECLARATION = [
        IrhpAppDataSource::DATA_KEY => [
            'key' => 'canMakeDeclaration',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_DECLINE = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'canBeDeclined',
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

    const PERMIT_APP_AWAITING_FEE = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'isAwaitingFee',
            'value' => true
        ],
    ];

    const PERMIT_APP_PAID =  [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'isFeePaid',
            'value' => true
        ],
    ];

    const PERMIT_APP_IS_VALID = [
        PermitAppDataSource::DATA_KEY => [
            'key' => 'isValid',
            'value' => true
        ],
    ];
}
