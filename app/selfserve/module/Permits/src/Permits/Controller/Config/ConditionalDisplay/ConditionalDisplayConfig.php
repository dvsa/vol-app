<?php

namespace Permits\Controller\Config\ConditionalDisplay;

use Permits\Controller\Config\DataSource\LicencesAvailable;
use Permits\Controller\Config\DataSource\AvailableTypes;
use Permits\Controller\Config\DataSource\AvailableYears;
use Permits\Controller\Config\DataSource\AvailableStocks;
use Permits\Controller\Config\DataSource\EcmtPermitApplicationWithLicences;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Controller\Config\DataSource\PermitsAvailable;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\DataSource\IrhpApplicationWithLicences;
use Permits\View\Helper\EcmtSection;
use Permits\View\Helper\IrhpApplicationSection;

/**
 * Holds conditional display configs that are used regularly
 */
class ConditionalDisplayConfig
{
    const PERMIT_APP_CAN_APPLY = [
        [
            'source' => AvailableTypes::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_WINDOW_CLOSED,
            'key' => 'hasTypes',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_SELECT_YEAR = [
        [
            'source' => AvailableYears::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_WINDOW_CLOSED,
            'key' => 'hasYears',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_SELECT_STOCK = [
        [
            'source' => AvailableStocks::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_WINDOW_CLOSED,
            'key' => 'hasStocks',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_APPLY_LICENCE = [
        [
            'source' => LicencesAvailable::DATA_KEY,
            'key' => 'hasOpenWindow',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_WINDOW_CLOSED,
        ],
        [
            'source' => LicencesAvailable::DATA_KEY,
            'key' => 'permitsAvailable',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_PERMITS_EXHAUSTED,
        ],
        [
            'source' => LicencesAvailable::DATA_KEY,
            'key' => 'hasEligibleLicences',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_NO_LICENCES,
        ],
    ];

    const PERMIT_APP_CAN_APPLY_LICENCE_EXISTING_APP = [
        [
            'source' => LicencesAvailable::DATA_KEY,
            'key' => 'hasOpenWindow',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_WINDOW_CLOSED,
        ],
        [
            'source' => LicencesAvailable::DATA_KEY,
            'key' => 'permitsAvailable',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_PERMITS_EXHAUSTED,
        ],
        [
            'source' => LicencesAvailable::DATA_KEY,
            'key' => 'hasEligibleLicences',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_NO_LICENCES,
        ],
        [
            'source' => LicencesAvailable::DATA_KEY,
            'key' => 'isNotYetSubmitted',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
        ],
    ];

    const PERMIT_APP_NOT_SUBMITTED = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true,
        ],
    ];

    const IRHP_APP_NOT_SUBMITTED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
    ];

    const IRHP_APP_CAN_PAY_APP_FEE = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeSubmitted',
            'value' => true
        ],
        [
            'source' => PermitsAvailable::DATA_KEY,
            'key' => 'permitsAvailable',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_PERMITS_EXHAUSTED,
        ],
    ];

    const IRHP_APP_SUBMITTED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => false
        ],
    ];

    const IRHP_APP_UNDER_CONSIDERATION = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isUnderConsideration',
            'value' => true
        ],
    ];

    const IRHP_APP_READY_FOR_COUNTRIES =  [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canUpdateCountries',
            'value' => true
        ],
    ];

    const IRHP_APP_READY_FOR_NO_OF_PERMITS = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isReadyForNoOfPermits',
            'value' => true
        ],
    ];

    const PERMIT_APP_CONFIRM_CHANGE_LICENCE = [
        [
            'source' => IrhpApplicationWithLicences::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
    ];

    const PERMIT_APP_CONFIRM_CHANGE_LICENCE_ECMT = [
        [
            'source' => EcmtPermitApplicationWithLicences::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_CHECK_ANSWERS = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'key' => 'canCheckAnswers',
            'value' => true,
            'route' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];

    const PERMIT_APP_CAN_MAKE_DECLARATION = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canMakeDeclaration',
            'value' => true
        ],
    ];

    const IRHP_APP_CAN_CHECK_ANSWERS = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'key' => 'canCheckAnswers',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
        [
            'source' => PermitsAvailable::DATA_KEY,
            'key' => 'permitsAvailable',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_PERMITS_EXHAUSTED,
        ],
    ];

    const IRHP_APP_CAN_BE_CANCELLED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeCancelled',
            'value' => true
        ],
    ];

    const IRHP_APP_IS_CANCELLED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isCancelled',
            'value' => true
        ],
    ];

    const IRHP_APP_CAN_BE_WITHDRAWN = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeWithdrawn',
            'value' => true
        ],
    ];

    const IRHP_APP_CAN_BE_DECLINED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeDeclined',
            'value' => true
        ],
    ];

    const IRHP_APP_IS_WITHDRAWN = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isWithdrawn',
            'value' => true
        ],
    ];

    const IRHP_APP_HAS_OUTSTANDING_FEES = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'hasOutstandingFees',
            'value' => true
        ],
    ];

    const IRHP_APP_IS_AWAITING_FEE = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isAwaitingFee',
            'value' => true
        ],
    ];

    const IRHP_APP_CAN_VIEW_CANDIDATE_PERMITS = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canViewCandidatePermits',
            'value' => true
        ],
    ];

    const IRHP_APP_IS_DECLINED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isDeclined',
            'value' => true
        ],
    ];

    const IRHP_APP_CAN_MAKE_DECLARATION = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canMakeDeclaration',
            'value' => true
        ],
        [
            'source' => PermitsAvailable::DATA_KEY,
            'key' => 'permitsAvailable',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_PERMITS_EXHAUSTED,
        ],
    ];

    const PERMIT_APP_CAN_DECLINE = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeDeclined',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_BE_CANCELLED = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeCancelled',
            'value' => true
        ],
    ];

    const PERMIT_APP_IS_CANCELLED = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isCancelled',
            'value' => true
        ],
    ];

    const PERMIT_APP_CAN_BE_WITHDRAWN = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeWithdrawn',
            'value' => true
        ],
    ];

    const PERMIT_APP_IS_WITHDRAWN = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isWithdrawn',
            'value' => true
        ],
    ];

    const PERMIT_APP_UNDER_CONSIDERATION = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isUnderConsideration',
            'value' => true
        ],
    ];

    const PERMIT_APP_AWAITING_FEE = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isAwaitingFee',
            'value' => true
        ],
    ];

    const PERMIT_APP_ISSUING = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isIssueInProgress',
            'value' => true
        ],
    ];

    const ECMT_APP_HAS_OUTSTANDING_FEES = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'hasOutstandingFees',
            'value' => true
        ],
    ];
}
