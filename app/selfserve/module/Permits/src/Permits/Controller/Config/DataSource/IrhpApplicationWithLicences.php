<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\AvailableLicences;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Irhp application available licences
 */
class IrhpApplicationWithLicences extends AbstractDataSource
{
    const DATA_KEY = 'licencesAvailable';
    protected $dto = AvailableLicences::class;
}
