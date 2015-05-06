<?php

/**
 * Nr Rest Helper
 */
namespace Olcs\Service\Nr;

use Zend\Http\Client as RestClient;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Json\Json;

/**
 * Nr Rest Helper
 */
class RestHelper implements FactoryInterface
{
    /**
     * @var \Zend\Http\Client
     */
    protected $restClient;

    /**
     * @param \Zend\Http\Client $restClient
     * @return $this
     */
    public function setRestClient(RestClient $restClient)
    {
        $this->restClient = $restClient;
        return $this;
    }

    /**
     * @return \Zend\Http\Client
     */
    public function getRestClient()
    {
        return $this->restClient;
    }

    /**
     * Sends Erru response back to INR system
     *
     * @param $caseId
     * @return \Zend\Http\Response
     */
    public function sendErruResponse($caseId)
    {
        $restClient = $this->getRestClient();
        $restClient->getUri()->setPath('/msi/send/' . $caseId);
        $response = $restClient->send();

        return $response;
    }

    /**
     * Sends tmId to the Nr endpoint, if it meets validation then a url will be returned
     *
     * @param $tmId
     * @return \Zend\Http\Response
     */
    public function fetchTmReputeUrl($tmId)
    {
        $restClient = $this->getRestClient();
        $restClient->getUri()->setPath('/repute/url/' . $tmId);
        $response = $restClient->send();

        $repute = Json::decode($response, Json::TYPE_ARRAY);

        if (isset($repute['Response']['Data']['url'])) {
            return $repute['Response']['Data']['url'];
        }

        return null;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @deprecated
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if (!isset($config['service_api_mapping']['endpoints']['nr'])) {
            throw new \RuntimeException('Missing NR rest client config');
        }

        $uri = $config['service_api_mapping']['endpoints']['nr'];

        $httpClient = new RestClient($uri);

        $this->setRestClient($httpClient);

        return $this;
    }
}
