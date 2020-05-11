<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpPermit\UniqueCountriesByLicence as UniqueCountriesDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Valid IRHP permits unique countries data source config
 */
class ValidIrhpPermitsUniqueCountries extends AbstractDataSource
{
    const DATA_KEY = 'validIrhpPermitsUniqueCountries';
    protected $dto = UniqueCountriesDto::class;
    protected $paramsMap = [
        'licence' => 'licence',
        'type' => 'irhpPermitType',
    ];
}
