<?php

namespace Common\Service\Data;

use Common\Service\Data\Interfaces\RestClientAware;
use Common\Util\RestClient;

/**
 * Class AbstractData
 * @package Olcs\Service\Data
 */
abstract class AbstractData implements RestClientAware
{
    /**
     * @var RestClient
     */
    protected $restClient;

    /**
     * @var string
     */
    protected $serviceName;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return $this
     */
    #[\Override]
    public function setRestClient(RestClient $restClient)
    {
        $this->restClient = $restClient;
        return $this;
    }

    /**
     * @return \Common\Util\RestClient
     */
    #[\Override]
    public function getRestClient()
    {
        return $this->restClient;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param $key
     * @param false|string[] $data
     *
     * @return $this
     *
     * @psalm-param 'results' $key
     * @psalm-param false|list{'results'} $data
     */
    public function setData(string $key, array|false $data)
    {
        $this->data[$key] = $data;
        return $this;
    }

    /**
     * @return array
     *
     * @psalm-param 'results' $key
     */
    public function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }
}
