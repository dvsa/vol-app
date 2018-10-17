<?php

namespace Permits\Controller\Config\Table;

use Permits\Controller\Config\DataSource\ValidEcmtPermits as ValidEcmtPermitsDataSource;

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
}
