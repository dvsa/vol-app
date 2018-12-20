<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpPermitType\ById as irhpPermitTypeDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Open windows data source config
 */
class IrhpPermitType extends AbstractDataSource
{
    const DATA_KEY = 'irhpPermitType';
    protected $dto = irhpPermitTypeDto::class;

    public function __construct()
    {
        $this->extraQueryData['id'] = 'permit_ecmt';
    }
}
