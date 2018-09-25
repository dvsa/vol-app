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
    protected $paramsMap = ['id' => 'id'];
    protected $defaultParamData = [];
    protected $extraQueryData = [];

    public function queryFromParams(array $inputParams): QueryInterface
    {
        //if we have defaults then set them here, these will be overwritten later if the parameter exists
        $paramData = $this->defaultParamData;

        foreach ($this->paramsMap as $param => $mappedTo) {
            if (isset($inputParams[$param])) {
                $paramData[$mappedTo] = $inputParams[$param];
            }
        }

        $queryData = array_merge($this->extraQueryData, $paramData);

        return $this->dto::create($queryData);
    }
}
