<?php

namespace Olcs\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Abstract controller data source. Hope this will be temporary while we move to olcs-data-source repo, or similar
 */
class AbstractDataSource implements DataSourceInterface
{
    const DATA_KEY = 'changeMe'; //key for when multiple sources are used within a single controller

    protected $dto;
    protected $params = ['id'];
    protected $extraQueryData = [];

    public function queryFromParams(array $inputParams): QueryInterface
    {
        $paramData = [];

        foreach ($this->params as $param) {
            $paramData[$param] = $inputParams[$param];
        }

        $queryData = array_merge($this->extraQueryData, $paramData);

        return $this->dto::create($queryData);
    }
}