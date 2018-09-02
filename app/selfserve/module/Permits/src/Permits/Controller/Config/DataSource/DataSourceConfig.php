<?php

namespace Permits\Controller\Config\DataSource;

use Permits\Controller\Config\DataSource\FeeList as FeeListDto;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Data\Mapper\FeeList as FeeListMapper;

/**
 * Holds data source configs that are used regularly
 */
class DataSourceConfig
{
    const PERMIT_APP = [
        PermitAppDataSource::class => [],
        FeeListDto::class => [
            'mapper' => FeeListMapper::class
        ],
    ];

    const PERMIT_APP_WITH_FEE_LIST = [
        PermitAppDataSource::class => [],
        FeeListDto::class => [
            'mapper' => FeeListMapper::class
        ],
    ];
}
