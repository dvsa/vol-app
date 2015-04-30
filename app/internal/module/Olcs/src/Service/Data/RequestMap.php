<?php

namespace Olcs\Service\Data;

use Common\Service\Data\Interfaces\RestClientAware;
use Common\Util\RestClient;

/**
 * Class RequestMap
 * @package Olcs\Sevice\Bus
 */
class RequestMap implements RestClientAware
{
    /**
     * @var RestClient
     */
    protected $restClient;

    /**
     * @param RestClient $restClient
     * @return mixed
     */
    public function setRestClient(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @return RestClient
     */
    public function getRestClient()
    {
        return $this->restClient;
    }

    /**
     * Should return the service name the rest client should connect to.
     *
     * @return string
     */
    public function getServiceName()
    {
        return 'ebsr\request-map';
    }

    public function requestMap($busRegId, $scale)
    {
        return $this->getRestClient()->post('', ['busRegId' => $busRegId, 'scale' => $scale]);
    }
}
