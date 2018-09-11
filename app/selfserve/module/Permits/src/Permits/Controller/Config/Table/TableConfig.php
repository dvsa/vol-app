<?php

namespace Permits\Controller\Config\Table;

use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;

/**
 * Holds data source configs that are used regularly
 */
class TableConfig
{
    const VALID_APP_OVERVIEW = [
        'overview-valid-permits' => [
            'tableName' => 'overview-valid-permits',
            'dataSource' => PermitAppDataSource::class
        ]
    ];

}
