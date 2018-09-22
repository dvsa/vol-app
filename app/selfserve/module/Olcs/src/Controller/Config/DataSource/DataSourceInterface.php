<?php

namespace Olcs\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Interface describing data sources
 */
interface DataSourceInterface
{
    public function queryFromParams(array $inputParams): QueryInterface;
}