<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\AvailableCountries as AvailableCountriesDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Available countries data source config
 */
class AvailableCountries extends AbstractDataSource
{
    const DATA_KEY = 'countries';
    protected $dto = AvailableCountriesDto::class;
}
