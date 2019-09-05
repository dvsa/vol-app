<?php

namespace Permits\Controller\Config\Table;

use Permits\Controller\Config\DataSource\IrhpFeeBreakdown as IrhpFeeBreakdownDataSource;
use Permits\Controller\Config\DataSource\UnpaidEcmtPermits as UnpaidEcmtPermitsDataSource;
use Permits\Controller\Config\DataSource\ValidEcmtPermits as ValidEcmtPermitsDataSource;
use Permits\Controller\Config\DataSource\ValidIrhpPermits as ValidIrhpPermitsDataSource;

/**
 * Holds data source configs that are used regularly
 */
class TableConfig
{
    const VALID_APP_OVERVIEW = [
        'overview-valid-permits' => [
            'tableName' => 'overview-valid-permits',
            'dataSource' => ValidEcmtPermitsDataSource::DATA_KEY
        ]
    ];
    const UNPAID_APP_OVERVIEW = [
        'overview-unpaid-permits' => [
            'tableName' => 'overview-unpaid-permits',
            'dataSource' => UnpaidEcmtPermitsDataSource::DATA_KEY
        ]
    ];
    const VALID_IRHP_PERMITS = [
        'valid-irhp-permits' => [
            'tableName' => 'valid-irhp-permits',
            'dataSource' => ValidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    const VALID_IRHP_PERMITS_BILATERAL = [
        'valid-irhp-permits' => [
            'tableName' => 'valid-irhp-permits-bilateral',
            'dataSource' => ValidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    const VALID_IRHP_PERMITS_ECMT_SHORT_TERM = [
        'valid-irhp-permits' => [
            'tableName' => 'valid-irhp-permits-ecmt-short-term',
            'dataSource' => ValidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
    const IRHP_FEE_BREAKDOWN = [
        'irhp-fee-breakdown' => [
            'tableName' => 'irhp-fee-breakdown',
            'dataSource' => IrhpFeeBreakdownDataSource::DATA_KEY
        ]
    ];
}
