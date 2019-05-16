<?php

namespace Permits\Controller\Config\DataSource;

use Common\RefData;
use Permits\Controller\Config\DataSource\FeeList as FeeListDto;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\DataSource\IrhpFeeBreakdown as IrhpFeeBreakdownDataSource;
use Permits\Controller\Config\DataSource\IrhpFeePerPermit as IrhpFeePerPermitDataSource;
use Permits\Controller\Config\DataSource\IrhpMaxStockPermits as IrhpMaxStockPermitsDataSource;
use Permits\Controller\Config\DataSource\ValidEcmtPermits as ValidEcmtPermitsDataSource;
use Permits\Controller\Config\DataSource\UnpaidEcmtPermits as UnpaidEcmtPermitsDataSource;
use Permits\Controller\Config\DataSource\ValidIrhpPermits as ValidIrhpPermitsDataSource;
use Permits\Data\Mapper\FeeList as FeeListMapper;
use Permits\Data\Mapper\ApplicationFees as ApplicationFeesMapper;
use Permits\Data\Mapper\AcceptOrDeclinePermits as AcceptOrDeclineMapper;
use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Permits\Data\Mapper\ValidEcmtPermits as ValidEcmtPermitsMapper;
use Permits\Data\Mapper\CheckAnswers as CheckAnswersMapper;
use Permits\Controller\Config\DataSource\EcmtConstrainedCountriesList as EcmtConstrainedCountriesDataSource;
use Permits\Data\Mapper\ValidEcmtPermitConstrainedCountries as EcmtConstrainedCountriesMapper;
use Permits\Data\Mapper\IrhpCheckAnswers as IrhpCheckAnswersMapper;

/**
 * Holds data source configs that are used regularly
 */
class DataSourceConfig
{
    const PERMIT_APP = [
        PermitAppDataSource::class => [],
    ];

    const PERMIT_APP_TYPE = [
        AvailableTypes::class => [],
        LicencesAvailable::class => [
            'passInData' => [
                'key' => 'id',
                'func' => 'getCurrentOrganisationId'
            ]
        ]
    ];

    const PERMIT_APP_ADD_LICENCE = [
        OpenWindows::class => [],
        LicencesAvailable::class => [
            'passInData' => [
                'key' => 'id',
                'func' => 'getCurrentOrganisationId'
            ]
        ],
        IrhpPermitType::class => []
    ];

    const PERMIT_APP_LICENCE = [
        IrhpAppDataSource::class => [],
        LicencesAvailable::class => [
            'passInData' => [
                'key' => 'id',
                'func' => 'getCurrentOrganisationId'
            ]
        ]
    ];

    const PERMIT_APP_ECMT_LICENCE = [
        PermitAppDataSource::class => [],
        LicencesAvailable::class => [
            'passInData' => [
                'key' => 'id',
                'func' => 'getCurrentOrganisationId'
            ]
        ]
    ];

    const PERMIT_APP_ECMT_EMISSIONS = [
        PermitAppDataSource::class => [],
        OpenWindows::class => [
            'passInData' => [
                'key' => 'type',
                'value' => RefData::ECMT_PERMIT_TYPE_ID
            ]
        ],
    ];

    const PERMIT_APP_SECTORS = [
        PermitAppDataSource::class => [],
        Sectors::class => []
    ];

    const PERMIT_APP_RESTRICTED_COUNTRIES = [
        PermitAppDataSource::class => [],
        EcmtConstrainedCountriesDataSource::class => []
    ];

    const PERMIT_APP_CHECK_ANSWERS = [
        PermitAppDataSource::class => [],
        OpenWindows::class => [
            'passInData' => [
                'key' => 'type',
                'value' => RefData::ECMT_PERMIT_TYPE_ID
            ],
            'append' => [
                PermitAppDataSource::DATA_KEY => CheckAnswersMapper::class
            ]
        ],
    ];

    const PERMIT_APP_FOR_ACCEPT_OR_DECLINE = [
        PermitAppDataSource::class => [
            'mapper' => AcceptOrDeclineMapper::class,
        ],
    ];

    const PERMIT_APP_WITH_FEES = [
        PermitAppDataSource::class => [
            'mapper' => ApplicationFeesMapper::class
        ],
    ];

    const PERMIT_APP_WITH_FEE_LIST = [
        PermitAppDataSource::class => [],
        FeeListDto::class => [
            'append' => [
                PermitAppDataSource::DATA_KEY => FeeListMapper::class
            ]
        ],
    ];

    const PERMIT_ECMT_VALID = [
        ValidEcmtPermitsDataSource::class => [
            'mapper' => ValidEcmtPermitsMapper::class,
        ],
        EcmtConstrainedCountriesDataSource::class => [
            'append' => [
                ValidEcmtPermitsDataSource::DATA_KEY => EcmtConstrainedCountriesMapper::class
            ]
        ],
    ];

    const PERMIT_ECMT_UNPAID = [
        UnpaidEcmtPermitsDataSource::class => [
            'mapper' => ValidEcmtPermitsMapper::class
        ],
        EcmtConstrainedCountriesDataSource::class => [
            'append' => [
                ValidEcmtPermitsDataSource::DATA_KEY => EcmtConstrainedCountriesMapper::class
            ]
        ],
    ];

    const IRHP_APP_OVERVIEW = [
        IrhpAppDataSource::class => [],
        QuestionAnswer::class => [],
    ];

    const IRHP_APP = [
        IrhpAppDataSource::class => [],
    ];

    const IRHP_APP_FEE = [
        IrhpAppDataSource::class => [
            'mapper' => IrhpApplicationFeeSummary::class
        ],
        IrhpFeeBreakdownDataSource::class => [],
    ];

    const IRHP_APP_COUNTRIES = [
        IrhpAppDataSource::class => [],
        AvailableCountries::class => [],
    ];

    const IRHP_VALID = [
        Licence::class => [],
        IrhpPermitType::class => [],
        ValidIrhpPermitsDataSource::class => [],
    ];

    const IRHP_APP_CHECK_ANSWERS = [
        IrhpAppDataSource::class => [
            'mapper' => IrhpCheckAnswersMapper::class
        ],
    ];

    const IRHP_APP_WITH_MAX_PERMITS_BY_STOCK = [
        IrhpAppDataSource::class => [],
        IrhpMaxStockPermitsDataSource::class => [],
        IrhpFeePerPermitDataSource::class => [],
    ];
}
