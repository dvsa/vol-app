<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\MaxPermittedReached as MaxPermittedReachedDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Max permitted reached data source config
 */
class MaxPermittedReached extends AbstractDataSource
{
    const DATA_KEY = 'maxPermittedReached';
    protected $dto = MaxPermittedReachedDto::class;
    protected $paramsMap = [
        'stock' => 'irhpPermitStock',
        'licence' => 'licence'
    ];
}
