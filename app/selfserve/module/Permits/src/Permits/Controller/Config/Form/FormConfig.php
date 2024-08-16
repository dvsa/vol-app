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
use Permits\Data\Mapper\IrhpFee as IrhpFeeMapper;
use Permits\Data\Mapper\AvailableBilateralStocks as AvailableBilateralStocksMapper;
use Permits\Data\Mapper\CandidatePermitSelection as CandidatePermitSelectionMapper;
use Permits\Data\Mapper\LicencesAvailable as LicencesAvailableMapper;
use Permits\Data\Mapper\NoOfPermits as NoOfPermitsMapper;
use Permits\Data\Mapper\RemovedCountries as RemovedCountriesMapper;

/**
 * Holds conditional display configs that are used regularly - eventually it'd be nice to have ::class in here
 * but we're limited by the quality of our current form building code
 */
class FormConfig
{
    public const FORM_CANCEL_PERMIT_APP_KEY = 'cancelPermitApp';
    public const FORM_WITHDRAW_PERMIT_APP_KEY = 'withdrawPermitApp';
    public const FORM_OPTIONS = 'mapForFormOptions';

    public const FORM_TYPE = [
        'type' => [
            'formClass' => 'TypeForm',
            'dataSource' => AvailableTypesDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => AvailableTypesMapper::class
            ]
        ],
    ];

    public const FORM_YEAR = [
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

    public const FORM_STOCK = [
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

    public const FORM_BILATERAL_STOCK = [
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

    public const FORM_ADD_LICENCE = [
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

    public const FORM_IRHP_DECLARATION = [
        'declaration' => [
            'formClass' => 'DeclarationForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
        ],
    ];

    public const FORM_FEE = [
        'fee' => [
            'formClass' => 'FeesForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => IrhpFeeMapper::class
            ]
        ],
    ];

    public const FORM_CANCEL_PERMIT_APP = [
        self::FORM_CANCEL_PERMIT_APP_KEY => [
            'formClass' => 'CancelApplicationForm',
        ],
    ];

    public const FORM_WITHDRAW_PERMIT_APP = [
        self::FORM_CANCEL_PERMIT_APP_KEY => [
            'formClass' => 'WithdrawApplicationForm',
        ],
    ];

    public const FORM_ACCEPT_AND_PAY = [
        'acceptAndPay' => [
            'formClass' => 'AcceptAndPayForm',
        ],
    ];

    public const FORM_DECLINE_PERMIT = [
        'decline' => [
            'formClass' => 'DeclineApplicationForm',
        ],
    ];

    public const FORM_COUNTRIES = [
        'countries' => [
            'formClass' => 'CountriesForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => AvailableCountriesMapper::class
            ]
        ],
    ];

    public const FORM_COUNTRIES_CONFIRMATION = [
        'countries' => [
            'formClass' => 'CountriesConfirmationForm',
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => RemovedCountriesMapper::class
            ]
        ],
    ];

    public const FORM_NO_OF_PERMITS = [
        'noOfPermits' => [
            'formClass' => 'NoOfPermitsForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => NoOfPermitsMapper::class
            ]
        ],
    ];

    public const FORM_IRHP_CHECK_ANSWERS = [
        'checkAnswers' => [
            'formClass' => 'CheckAnswersForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
        ],
    ];

    public const FORM_IRHP_IPA_CHECK_ANSWERS = [
        'checkAnswers' => [
            'formClass' => 'IpaCheckAnswersForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
        ],
    ];

    public const FORM_CANCEL_IRHP_APP = [
        self::FORM_CANCEL_PERMIT_APP_KEY => [
            'formClass' => 'CancelApplicationForm',
        ],
    ];

    public const FORM_IRHP_OVERVIEW_SUBMIT = [
        'irhpOverviewSubmit' => [
            'formClass' => 'IrhpOverviewSubmitForm',
        ]
    ];

    public const FORM_CANDIDATE_PERMIT_SELECTION = [
        'candidatePermitSelection' => [
            'formClass' => 'CandidatePermitSelectionForm',
            'dataSource' => IrhpApplicationDataSource::DATA_KEY,
            'mapper' => [
                'type' => self::FORM_OPTIONS,
                'class' => CandidatePermitSelectionMapper::class
            ]
        ],
    ];
}
