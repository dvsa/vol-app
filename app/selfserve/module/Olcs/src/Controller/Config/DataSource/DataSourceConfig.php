<?php

namespace Olcs\Controller\Config\DataSource;

use Olcs\Controller\Config\DataSource\Licence as LicenceDto;

/**
 * Holds data source configs that are used regularly
 */
class DataSourceConfig
{
    public const LICENCE = [
        LicenceDto::class => []
    ];

    public const SURRENDER = [
        Surrender::class => []
    ];
}
