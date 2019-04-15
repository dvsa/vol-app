<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpPermitType\ById as irhpPermitTypeDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Irhp Permit Type data source config
 */
class IrhpPermitType extends AbstractDataSource
{
    const DATA_KEY = 'irhpPermitType';
    protected $dto = irhpPermitTypeDto::class;

    protected $paramsMap = ['type' => 'id'];
}
