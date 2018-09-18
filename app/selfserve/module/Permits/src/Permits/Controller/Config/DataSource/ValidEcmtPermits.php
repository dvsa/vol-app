<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\ValidEcmtPermits as ValidEcmtPermitsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Valid ECMT permits data source config
 */
class ValidEcmtPermits extends AbstractDataSource
{
    const DATA_KEY = 'application';
    protected $dto = ValidEcmtPermitsDto::class;
    protected $paramsMap = ['id' => 'id', 'page' => 'page', 'limit' => 'limit'];
}
