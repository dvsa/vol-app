<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Licence data source config
 */
class Licence extends AbstractDataSource
{
    const DATA_KEY = 'licence';
    protected $dto = LicenceDto::class;

    protected $paramsMap = ['licence' => 'id'];
}
