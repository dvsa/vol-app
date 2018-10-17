<?php

namespace Permits\Controller\Config\DataSource;

use Permits\Controller\Config\DataSource\FeeList as FeeListDto;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Controller\Config\DataSource\ValidEcmtPermits as ValidEcmtPermitsDataSource;
use Permits\Controller\Config\DataSource\UnpaidEcmtPermits as UnpaidEcmtPermitsDataSource;
use Permits\Data\Mapper\FeeList as FeeListMapper;
use Permits\Data\Mapper\ValidEcmtPermits as ValidEcmtPermitsMapper;
use Permits\Data\Mapper\CheckAnswers as CheckAnswersMapper;
use Permits\Controller\Config\DataSource\EcmtConstrainedCountriesList as EcmtConstrainedCountriesDataSource;
use Permits\Data\Mapper\EcmtConstrainedCountries as EcmtConstrainedCountriesMapper;


/**
 * Holds data source configs that are used regularly
 */
class DataSourceConfig
{
    const PERMIT_APP = [
        PermitAppDataSource::class => [],
    ];
    const PERMIT_APP_WITH_FEE_LIST = [
        PermitAppDataSource::class => [],
        FeeListDto::class => [
            'mapper' => FeeListMapper::class
        ],
    ];
    const PERMIT_ECMT_VALID = [
        EcmtConstrainedCountriesDataSource::class => [],
        ValidEcmtPermitsDataSource::class => [
            'mapper' => ValidEcmtPermitsMapper::class,
            'append' => [
                EcmtConstrainedCountriesDataSource::DATA_KEY => EcmtConstrainedCountriesMapper::class
            ]
        ],
    ];
    const PERMIT_ECMT_UNPAID = [
        UnpaidEcmtPermitsDataSource::class => [
            'mapper' => ValidEcmtPermitsMapper::class
        ],
    ];
    const PERMIT_APP_CHECK_ANSWERS = [
        PermitAppDataSource::class => [
            'mapper' => CheckAnswersMapper::class
        ],
    ];
}
