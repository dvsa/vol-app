<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\Sectors as SectorsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Open windows data source config
 */
class Sectors extends AbstractDataSource
{
    const DATA_KEY = 'sectors';
    protected $dto = SectorsDto::class;

    protected $defaultParamData = [
        'sort' => 'displayOrder',
        'order' => 'ASC'
    ];
}
