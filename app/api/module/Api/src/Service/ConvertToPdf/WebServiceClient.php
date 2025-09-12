<?php

namespace Dvsa\Olcs\Api\Service\ConvertToPdf;

use Laminas\Http\Client as HttpClient;
use Dvsa\Olcs\Api\Domain\Exception\RestResponseException;

/**
 * Class InrClient
 * @package Dvsa\Olcs\Api\Service\Nr
 */
class WebServiceClient implements ConvertToPdfInterface
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Constructor
     *
     * @param HttpClient $httpClient Httpd client to use
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->setHttpClient($httpClient);
    }

    /**
     * Get the HTTP Client
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Set the HTTP Client
     *
     * @param HttpClient $httpClient HTTP client
     *
     * @return void
     */
    public function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;
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
        $this->getHttpClient()->reset();
        $this->getHttpClient()->setMethod(\Laminas\Http\Request::METHOD_POST);
        $this->getHttpClient()->setFileUpload($fileName, 'file');

        $response = $this->getHttpClient()->send();
        if (!$response->isOk()) {
            $body = json_decode($response->getBody());
            $message = is_object($body) && isset($body->Message) ?
                $body->Message :
                $response->getReasonPhrase();

            throw new RestResponseException(
                'ConvertToPdf failed, web service response : ' . $message,
                $response->getStatusCode()
            );
        }

        file_put_contents($destination, $response->getBody());
    }
}
