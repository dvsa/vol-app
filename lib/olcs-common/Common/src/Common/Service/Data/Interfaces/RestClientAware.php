<?php

namespace Common\Service\Data\Interfaces;

use Common\Util\RestClient;

/**
 * Interface RestClientAware
 * @package Common\Service\Data
 */
interface RestClientAware
{
    /**
     * @return mixed
     */
    public function setRestClient(RestClient $restClient);

    /**
     * @return RestClient
     */
    public function getRestClient();

    /**
     * Should return the service name the rest client should connect to.
     *
     * @return string
     */
    public function getServiceName();
}
