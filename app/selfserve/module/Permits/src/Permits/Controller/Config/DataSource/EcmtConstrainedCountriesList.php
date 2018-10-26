<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\EcmtConstrainedCountriesList as EcmtConstrainedCountriesListDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Valid ECMT permits data source config
 */
class EcmtConstrainedCountriesList extends AbstractDataSource
{
    const DATA_KEY = 'ecmtConstrainedCountries';
    protected $dto = EcmtConstrainedCountriesListDto::class;
    protected $paramsMap = [];
    protected $defaultParamData = [
        'hasEcmtConstraints' => 1
    ];
}
