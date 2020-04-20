<?php

namespace Permits\Controller\Config\DataSource;

use Permits\Controller\Config\DataSource\AvailableBilateralStocks as AvailableBilateralStocksDataSource;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\DataSource\IrhpApplicationWithLicences as IrhpApplicationWithLicencesDataSource;
use Permits\Controller\Config\DataSource\IrhpFeeBreakdown as IrhpFeeBreakdownDataSource;
use Permits\Controller\Config\DataSource\IrhpFeePerPermit as IrhpFeePerPermitDataSource;
use Permits\Controller\Config\DataSource\IrhpMaxStockPermits as IrhpMaxStockPermitsDataSource;
use Permits\Controller\Config\DataSource\UnpaidIrhpPermits as UnpaidIrhpPermitsDataSource;
use Permits\Controller\Config\DataSource\ValidIrhpPermits as ValidIrhpPermitsDataSource;
use Permits\Controller\Config\DataSource\PermitsAvailable as PermitsAvailableDataSource;
use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Permits\Data\Mapper\PermitTypeTitle as PermitTypeTitleMapper;

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
    ];

    const PERMIT_APP_YEAR = [
        AvailableYears::class => [],
    ];

    const PERMIT_APP_STOCK = [
        AvailableStocks::class => [],
    ];

    const PERMIT_APP_ADD_LICENCE = [
        LicencesAvailable::class => [
            'passInData' => [
                'key' => 'id',
                'func' => 'getCurrentOrganisationId'
            ]
        ],
    ];

    const PERMIT_APP_CHANGE_LICENCE = [
        IrhpApplicationWithLicencesDataSource::class => [],
    ];

    const IRHP_APP_OVERVIEW = [
        IrhpAppDataSource::class => [
            'mapper' => PermitTypeTitleMapper::class
        ],
        QuestionAnswer::class => [],
    ];

    const IRHP_APP = [
        IrhpAppDataSource::class => [
            'mapper' => PermitTypeTitleMapper::class
        ],
    ];

    const IRHP_APP_FEE = [
        IrhpAppDataSource::class => [],
        IrhpFeeBreakdownDataSource::class => [],
        PermitsAvailableDataSource::class => [],
    ];

    const IRHP_APP_UNDER_CONSIDERATION = [
        IrhpAppDataSource::class => [
            'mapper' => IrhpApplicationFeeSummary::class
        ],
    ];

    const IRHP_APP_AWAITING_FEE = [
        IrhpAppDataSource::class => [
            'mapper' => IrhpApplicationFeeSummary::class
        ],
    ];

    const IRHP_APP_COUNTRIES = [
        IrhpAppDataSource::class => [],
        AvailableCountries::class => [],
    ];

    const IRHP_APP_ESSENTIAL_INFORMATION = [
        IrhpAppDataSource::class => [],
        BilateralFeesByCountry::class => [],
    ];

    const IRHP_APP_PERIODS = [
        IrhpAppDataSource::class => [],
        AvailableBilateralStocksDataSource::class => [],
    ];

    const IRHP_UNPAID_PERMITS = [
        IrhpAppDataSource::class => [],
        UnpaidIrhpPermitsDataSource::class => [],
    ];

    const IRHP_VALID = [
        Licence::class => [],
        IrhpPermitType::class => [],
        ValidIrhpPermitsDataSource::class => [],
    ];

    const IRHP_APP_CHECK_ANSWERS = [
        IrhpAppDataSource::class => [
            'mapper' => PermitTypeTitleMapper::class
        ],
        PermitsAvailableDataSource::class => [],
    ];

    const IRHP_APP_DECLARATION = [
        IrhpAppDataSource::class => [
            'mapper' => PermitTypeTitleMapper::class
        ],
        PermitsAvailableDataSource::class => [],
    ];

    const IRHP_APP_WITH_MAX_PERMITS_BY_STOCK = [
        IrhpAppDataSource::class => [],
        IrhpMaxStockPermitsDataSource::class => [],
        IrhpFeePerPermitDataSource::class => [],
    ];
}
