<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Organisation\OrganisationAvailableLicences as OrganisationDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Licences available data source config
 */
class LicencesAvailable extends AbstractDataSource
{
    const DATA_KEY = 'licencesAvailable';
    protected $dto = OrganisationDto::class;
    protected $paramsMap = [
        'id' => 'id',
        'type' => 'irhpPermitType',
        'stock' => 'irhpPermitStock',
    ];
}
