<?php

namespace Permits\Controller\Config\Table;

use Permits\Controller\Config\DataSource\IrhpFeeBreakdown as IrhpFeeBreakdownDataSource;
use Permits\Controller\Config\DataSource\UnpaidIrhpPermits as UnpaidIrhpPermitsDataSource;
use Permits\Controller\Config\DataSource\ValidIrhpPermits as ValidIrhpPermitsDataSource;
use Permits\Controller\Config\DataSource\WantedUnpaidIrhpPermits as WantedUnpaidIrhpPermitsDataSource;

/**
 * Holds data source configs that are used regularly
 */
class TableConfig
{
    public const UNPAID_IRHP_PERMITS = [
        'unpaid-irhp-permits' => [
            'tableName' => 'unpaid-irhp-permits',
            'dataSource' => UnpaidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    public const WANTED_UNPAID_IRHP_PERMITS = [
        'unpaid-irhp-permits' => [
            'tableName' => 'wanted-unpaid-irhp-permits',
            'dataSource' => WantedUnpaidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    public const VALID_IRHP_PERMITS = [
        'valid-irhp-permits' => [
            'tableName' => 'valid-irhp-permits',
            'dataSource' => ValidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    public const VALID_IRHP_PERMITS_BILATERAL = [
        'valid-irhp-permits' => [
            'tableName' => 'valid-irhp-permits-bilateral',
            'dataSource' => ValidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    public const VALID_IRHP_PERMITS_MULTILATERAL = [
        'valid-irhp-permits' => [
            'tableName' => 'valid-irhp-permits-multilateral',
            'dataSource' => ValidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    public const VALID_IRHP_PERMITS_ECMT_SHORT_TERM = [
        'valid-irhp-permits' => [
            'tableName' => 'valid-irhp-permits-ecmt-short-term',
            'dataSource' => ValidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    public const VALID_IRHP_PERMITS_ECMT_REMOVAL = [
        'valid-irhp-permits' => [
            'tableName' => 'valid-irhp-permits-ecmt-removal',
            'dataSource' => ValidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    public const IRHP_FEE_BREAKDOWN = [
        'irhp-fee-breakdown' => [
            'tableName' => 'changeMe',
            'dataSource' => IrhpFeeBreakdownDataSource::DATA_KEY
        ]
    ];
}
