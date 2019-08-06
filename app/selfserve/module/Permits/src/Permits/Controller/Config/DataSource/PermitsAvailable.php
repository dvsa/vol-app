<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\PermitsAvailable as PermitsAvailableDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Permits available data source config
 */
class PermitsAvailable extends AbstractDataSource
{
    const DATA_KEY = 'permitsAvailable';
    protected $dto = PermitsAvailableDto::class;
}
