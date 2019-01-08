<?php

namespace Permits\Controller\Config\DataSource;

use Permits\Controller\Config\DataSource\FeeList as FeeListDto;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\DataSource\ValidEcmtPermits as ValidEcmtPermitsDataSource;
use Permits\Controller\Config\DataSource\UnpaidEcmtPermits as UnpaidEcmtPermitsDataSource;
use Permits\Data\Mapper\FeeList as FeeListMapper;
use Permits\Data\Mapper\ApplicationFees as ApplicationFeesMapper;
use Permits\Data\Mapper\AcceptOrDeclinePermits as AcceptOrDeclineMapper;
use Permits\Data\Mapper\ValidEcmtPermits as ValidEcmtPermitsMapper;
use Permits\Data\Mapper\CheckAnswers as CheckAnswersMapper;
use Permits\Controller\Config\DataSource\EcmtConstrainedCountriesList as EcmtConstrainedCountriesDataSource;
use Permits\Data\Mapper\ValidEcmtPermitConstrainedCountries as EcmtConstrainedCountriesMapper;

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
        LastOpenWindow::class => [],
        LicencesAvailable::class => [
            'passInUserData' => 'getCurrentOrganisationId'
        ]
    ];

    const PERMIT_APP_ADD_LICENCE = [
        AvailableTypes::class => [],
        LastOpenWindow::class => [],
        LicencesAvailable::class => [
            'passInUserData' => 'getCurrentOrganisationId'
        ]
    ];

    const PERMIT_APP_LICENCE = [
        PermitAppDataSource::class => [],
        LicencesAvailable::class => [
            'passInUserData' => 'getCurrentOrganisationId',
        ]
    ];

    const PERMIT_APP_SECTORS = [
        PermitAppDataSource::class => [],
        Sectors::class => []
    ];

    const PERMIT_APP_CHECK_ANSWERS = [
        PermitAppDataSource::class => [
            'mapper' => CheckAnswersMapper::class
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
            'mapper' => FeeListMapper::class
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

    const IRHP_APP = [
        IrhpAppDataSource::class => [],
    ];
}
