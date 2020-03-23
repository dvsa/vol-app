<?php

namespace Permits\Controller\Config\Form;

use Permits\Controller\Config\DataSource\AvailableBilateralStocks as AvailableBilateralStocksDataSource;
use Permits\Controller\Config\DataSource\AvailableTypes as AvailableTypesDataSource;
use Permits\Controller\Config\DataSource\AvailableYears as AvailableYearsDataSource;
use Permits\Controller\Config\DataSource\AvailableStocks as AvailableStocksDataSource;
use Permits\Controller\Config\DataSource\LicencesAvailable as LicencesAvailableDataSource;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;
use Permits\Data\Mapper\AvailableCountries as AvailableCountriesMapper;
use Permits\Data\Mapper\AvailableTypes as AvailableTypesMapper;
use Permits\Data\Mapper\AvailableYears as AvailableYearsMapper;
use Permits\Data\Mapper\AvailableStocks as AvailableStocksMapper;
use Permits\Data\Mapper\AvailableBilateralStocks as AvailableBilateralStocksMapper;
use Permits\Data\Mapper\LicencesAvailable as LicencesAvailableMapper;
use Permits\Data\Mapper\NoOfPermits as NoOfPermitsMapper;
use Permits\Data\Mapper\ChangeLicence as ChangeLicenceMapper;

/**
 * Holds conditional display configs that are used regularly - eventually it'd be nice to have ::class in here
 * but we're limited by the quality of our current form building code
 */
class FormConfig
{
    const FORM_CANCEL_PERMIT_APP_KEY = 'cancelPermitApp';
    const FORM_WITHDRAW_PERMIT_APP_KEY = 'withdrawPermitApp';
    const FORM_OPTIONS = 'mapForFormOptions';

    const FORM_TYPE = [
        'type' => [
            'formClass' => 'TypeForm',
            'dataSource' => AvailableTypesDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => AvailableTypesMapper::class
            ]
        ],
    ];

    const FORM_YEAR = [
        'sectors' => [
            'formClass' => 'YearForm',
            'dataRouteParam' => 'type',
            'dataSource' => AvailableYearsDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => AvailableYearsMapper::class
            ]
        ],
    ];

    const FORM_STOCK = [
        'sectors' => [
            'formClass' => 'StockForm',
            'dataRouteParam' => 'type',
            'dataSource' => AvailableStocksDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => AvailableStocksMapper::class
            ]
        ],
    ];

    const FORM_BILATERAL_STOCK = [
        'stocks' => [
            'formClass' => 'PeriodSelect',
            'dataRouteParam' => 'type',
            'dataSource' => AvailableBilateralStocksDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => AvailableBilateralStocksMapper::class
            ]
        ],
    ];

    const FORM_ADD_LICENCE = [
        'licence' => [
            'formClass' => 'LicenceForm',
            'dataParam' => 'active',
            'dataSource' => LicencesAvailableDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => LicencesAvailableMapper::class
            ]
        ],
    ];

    const FORM_LICENCE = [
        'licence' => [
            'formClass' => 'LicenceForm',
            'dataParam' => 'active',
            'dataSource' => LicencesAvailableDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => LicencesAvailableMapper::class
            ]
        ],
    ];

    const FORM_CONFIRM_CHANGE_LICENCE = [
        'licence' => [
            'formClass' => 'ChangeLicenceForm',
            'dataRouteParam' => 'licence',
            'dataSource' => LicencesAvailableDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => ChangeLicenceMapper::class
            ]
        ],
    ];

    const FORM_IRHP_DECLARATION = [
        'declaration' => [
            'formClass' => 'DeclarationForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
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

    const FORM_DECLINE_PERMIT = [
        'decline' => [
            'formClass' => 'DeclineApplicationForm',
        ],
    ];

    const FORM_COUNTRIES = [
        'countries' => [
            'formClass' => 'CountriesForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => AvailableCountriesMapper::class
            ]
        ],
    ];

    const FORM_NO_OF_PERMITS = [
        'noOfPermits' => [
            'formClass' => 'NoOfPermitsForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => NoOfPermitsMapper::class
            ]
        ],
    ];

    const FORM_IRHP_CHECK_ANSWERS = [
        'checkAnswers' => [
            'formClass' => 'CheckAnswersForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
        ],
    ];

    const FORM_CANCEL_IRHP_APP = [
        self::FORM_CANCEL_PERMIT_APP_KEY => [
            'formClass' => 'CancelApplicationForm',
        ],
    ];
}
