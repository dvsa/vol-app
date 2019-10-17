<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\AvailableLicences;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * ECMT permit application with available licences
 */
class EcmtPermitApplicationWithLicences extends AbstractDataSource
{
    const DATA_KEY = 'licencesAvailable';
    protected $dto = AvailableLicences::class;
}
