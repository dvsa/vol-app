<?php

namespace Dvsa\Olcs\Api\Service\ConvertToPdf;

use Laminas\Http\Client as HttpClient;
use Laminas\Http\Request;
use Dvsa\Olcs\Api\Domain\Exception\RestResponseException;

class GotenbergClient implements ConvertToPdfInterface
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * Constructor
     *
     * @param HttpClient $httpClient Http client to use
     * @param string $baseUri Base URI for Gotenberg service
     */
    public function __construct(HttpClient $httpClient, string $baseUri)
    {
        $this->httpClient = $httpClient;
        $this->baseUri = rtrim($baseUri, '/');
    }

    /**
     * Convert a document to a PDF
     *
     * @param string $fileName    File to be converted
     * @param string $destination Destination file, the PDF file name
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RestResponseException
     */
    public function convert($fileName, $destination)
    {
        $this->httpClient->reset();
        $this->httpClient->setUri($this->baseUri . '/forms/libreoffice/convert');
        $this->httpClient->setMethod(Request::METHOD_POST);
        $this->httpClient->setFileUpload($fileName, 'files');

        $response = $this->httpClient->send();
        
        if (!$response->isOk()) {
            $body = $response->getBody();
            $message = $body ?: $response->getReasonPhrase();

            throw new RestResponseException(
                'ConvertToPdf failed, Gotenberg service response : ' . $message,
                $response->getStatusCode()
            );
        }

        file_put_contents($destination, $response->getBody());
    }
}