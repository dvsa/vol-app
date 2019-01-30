<?php

namespace Permits\Controller\Config\Table;

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
        'overview-valid-permits' => [
            'tableName' => 'overview-unpaid-permits',
            'dataSource' => ValidEcmtPermitsDataSource::DATA_KEY
        ]
    ];
    const VALID_IRHP_OVERVIEW = [
        'overview-irhp-permits' => [
            'tableName' => 'overview-irhp-permits',
            'dataSource' => ValidIrhpPermitsDataSource::DATA_KEY
        ]
    ];
}
