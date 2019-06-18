<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\ValidEcmtPermits as ValidEcmtPermitsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Valid ECMT permits data source config
 */
class ValidEcmtPermits extends AbstractDataSource
{
    const DATA_KEY = 'validPermits';
    protected $dto = ValidEcmtPermitsDto::class;
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
