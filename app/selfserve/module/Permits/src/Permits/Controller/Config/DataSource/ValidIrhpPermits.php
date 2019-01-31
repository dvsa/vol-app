<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence as ValidIrhpPermitsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Valid IRHP permits data source config
 */
class ValidIrhpPermits extends AbstractDataSource
{
    const DATA_KEY = 'validIrhpPermits';
    protected $dto = ValidIrhpPermitsDto::class;
    protected $paramsMap = [
        'licence' => 'licence',
        'page' => 'page',
        'limit' => 'limit',
    ];
    protected $defaultParamData = [
        'page' => 1,
        'limit' => 10,
    ];
}
