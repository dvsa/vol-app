<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Organisation\OrganisationPermits as OrganisationDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Open windows data source config
 */
class LicencesAvailable extends AbstractDataSource
{
    const DATA_KEY = 'licencesAvailable';
    protected $dto = OrganisationDto::class;
    protected $paramsMap = [
        'id' => 'id',
        'year' => 'year'
    ];
}
