<?php

namespace Dvsa\Olcs\Api\Service\Nr;

use Laminas\Http\Client as RestClient;
use Laminas\Http\Request;
use Olcs\Logging\Log\Logger;

class InrClient implements InrClientInterface
{
    public function __construct(protected RestClient $restClient)
    {
    }

    public function makeRequest(string $xml): int
    {
        $this->restClient->setEncType('text/xml');
        $this->restClient->getRequest()->setMethod(Request::METHOD_POST);
        $this->restClient->getRequest()->setContent($xml);

        Logger::info('INR request', ['data' => $this->restClient->getRequest()->toString()]);

        $response = $this->restClient->send();

        Logger::info('INR response', ['data' => $response->toString()]);

        return $response->getStatusCode();
    }

    public function getRestClient(): RestClient
    {
        return $this->restClient;
    }

    /**
     * close connection to INR
     */
    public function close(): void
    {
        $this->restClient->getAdapter()->close();
    }
}
