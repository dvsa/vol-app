<?php

namespace Permits\Controller\Config\ConditionalDisplay;

use Permits\Controller\Config\DataSource\AvailableStocks;
use Permits\Controller\Config\DataSource\AvailableTypes;
use Permits\Controller\Config\DataSource\AvailableYears;
use Permits\Controller\Config\DataSource\BilateralCountryAccessible;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\DataSource\LicencesAvailable;
use Permits\Controller\Config\DataSource\MaxPermittedReachedForStock;
use Permits\Controller\Config\DataSource\MaxPermittedReachedForType;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Controller\Config\DataSource\PermitsAvailable;
use Permits\View\Helper\IrhpApplicationSection;

/**
 * Holds conditional display configs that are used regularly
 */
class ConditionalDisplayConfig
{
    public const PERMIT_APP_CAN_APPLY = [
        [
            'source' => AvailableTypes::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_WINDOW_CLOSED,
            'key' => 'hasTypes',
            'value' => true
        ],
    ];

    public const PERMIT_APP_CAN_SELECT_YEAR = [
        [
            'source' => AvailableYears::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_WINDOW_CLOSED,
            'key' => 'hasYears',
            'value' => true
        ],
        [
            'source' => MaxPermittedReachedForType::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_MAX_PERMITTED_REACHED_FOR_TYPE,
            'key' => 'maxPermittedReached',
            'value' => false
        ],
    ];

    public const PERMIT_APP_CAN_SELECT_STOCK = [
        [
            'source' => AvailableStocks::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_WINDOW_CLOSED,
            'key' => 'hasStocks',
            'value' => true
        ],
    ];

    public const PERMIT_APP_CAN_APPLY_LICENCE = [
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

    public const PERMIT_APP_CAN_SHOW_MAX_PERMITTED_REACHED_FOR_TYPE = [
        [
            'source' => MaxPermittedReachedForType::DATA_KEY,
            'key' => 'maxPermittedReached',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
        ],
    ];

    public const PERMIT_APP_CAN_SHOW_MAX_PERMITTED_REACHED_FOR_STOCK = [
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
            'source' => MaxPermittedReachedForStock::DATA_KEY,
            'key' => 'maxPermittedReached',
            'value' => true,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
        ],
    ];

    public const IRHP_APP_NOT_SUBMITTED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
    ];

    public const IRHP_BILATERAL_APP_NOT_SUBMITTED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isBilateral',
            'value' => true
        ],
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
    ];

    public const IRHP_APP_OVERVIEW_ACCESSIBLE = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_COUNTRIES,
            'key' => 'isOverviewAccessible',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_VIEW_ESSENTIAL_INFORMATION = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isBilateral',
            'value' => true
        ],
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
        [
            'source' => BilateralCountryAccessible::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isAccessible',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_SELECT_BILATERAL_PERIOD = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isBilateral',
            'value' => true
        ],
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
        [
            'source' => BilateralCountryAccessible::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isAccessible',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_PAY_APP_FEE = [
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

    public const IRHP_APP_SUBMITTED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => false
        ],
    ];

    public const IRHP_APP_UNDER_CONSIDERATION = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isUnderConsideration',
            'value' => true
        ],
    ];

    public const IRHP_APP_READY_FOR_COUNTRIES =  [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canUpdateCountries',
            'value' => true
        ],
    ];

    public const IRHP_APP_READY_FOR_NO_OF_PERMITS = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isReadyForNoOfPermits',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_CHECK_ANSWERS = [
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

    public const IRHP_IPA_CAN_CHECK_ANSWERS = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isNotYetSubmitted',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_BE_CANCELLED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeCancelled',
            'value' => true
        ],
    ];

    public const IRHP_APP_IS_CANCELLED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isCancelled',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_BE_WITHDRAWN = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeWithdrawn',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_BE_DECLINED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canBeDeclined',
            'value' => true
        ],
    ];

    public const IRHP_APP_IS_WITHDRAWN = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isWithdrawn',
            'value' => true
        ],
    ];

    public const IRHP_APP_HAS_OUTSTANDING_FEES = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'hasOutstandingFees',
            'value' => true
        ],
    ];

    public const IRHP_APP_IS_AWAITING_FEE = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isAwaitingFee',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_VIEW_CANDIDATE_PERMITS = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canViewCandidatePermits',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_SELECT_CANDIDATE_PERMITS = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'canSelectCandidatePermits',
            'value' => true
        ],
    ];

    public const IRHP_APP_IS_DECLINED = [
        [
            'source' => IrhpAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isDeclined',
            'value' => true
        ],
    ];

    public const IRHP_APP_CAN_MAKE_DECLARATION = [
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

    public const PERMIT_APP_ISSUING = [
        [
            'source' => PermitAppDataSource::DATA_KEY,
            'route' => IrhpApplicationSection::ROUTE_PERMITS,
            'key' => 'isIssueInProgress',
            'value' => true
        ],
    ];
}
