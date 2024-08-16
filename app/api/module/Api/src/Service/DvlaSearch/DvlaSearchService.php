<?php

namespace Dvsa\Olcs\Api\Service\DvlaSearch;

use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\BadRequestException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\BadResponseException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\NotFoundException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\ServiceException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\VehicleUnavailableException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Mapper\DvlaVehicleResponseToModelMapper;
use Dvsa\Olcs\Api\Service\DvlaSearch\Model\DvlaVehicle;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface as Logger;

use function GuzzleHttp\json_decode as json_decode;
use function GuzzleHttp\json_encode as json_encode;

/**
 * Class Client
 */
class DvlaSearchService
{
    /**
     * Prefix for messages for exceptions caused by the broker instead of DVLA
     */
    protected const BROKER_EXCEPTION_PREFIX = "DVLA Search Broker API:";

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Client constructor.
     */
    public function __construct(HttpClient $httpClient, private readonly ?Logger $logger = null)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return DvlaVehicle
     * @throws BadResponseException
     * @throws GuzzleException
     * @throws ServiceException
     */
    public function getVehicle(string $vrn): DvlaVehicle
    {
        $options = [
            'query' => [
                'identifier' => $vrn
            ]
        ];

        $response = $this->request('GET', 'vehicle', $options);

        $json = $response->getBody()->getContents();
        $mapper = new DvlaVehicleResponseToModelMapper();

        try {
            $responseArray = json_decode($json, true);
        } catch (InvalidArgumentException) {
            throw new BadResponseException($this->generateBrokerExceptionMessage("JSON response cannot be parsed"));
        }

        return $mapper->map($responseArray);
    }

    /**
     * @param array<string, mixed> $options
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws VehicleUnavailableException
     * @throws ServiceException
     */
    protected function request(string $method, string $route, array $options = []): ResponseInterface
    {
        try {
            $response = $this->httpClient->request($method, $route, $options);
            if ($response->getStatusCode() === 204) {
                throw new VehicleUnavailableException("Vehicle data unavailable from DVLA API");
            }
            return $response;
        } catch (GuzzleBadResponseException $exception) {
            $response = $exception->getResponse();
            throw $this->generateServiceException($exception);
        } finally {
            if (!is_null($response)) {
                $this->logResponse($response, $route, $options);
            }
        }
    }

    /**
     * @return ServiceException
     */
    protected function generateServiceException(GuzzleBadResponseException $exception): ServiceException
    {
        $response = $exception->getResponse();

        if (is_null($response)) {
            return new ServiceException(
                $this->generateBrokerExceptionMessage("Server Error"),
                $exception->getCode(),
                $exception
            );
        }

        return match ($response->getStatusCode() ?? 500) {
            400 => new BadRequestException(
                $this->generateBrokerExceptionMessage("Bad request"),
                $exception->getCode(),
                $exception
            ),
            403 => new ForbiddenException(
                $this->generateBrokerExceptionMessage("API key is invalid or not defined"),
                $exception->getCode(),
                $exception
            ),
            404 => new NotFoundException(
                $this->generateBrokerExceptionMessage("URI Not Found"),
                $exception->getCode(),
                $exception
            ),
            default => new ServiceException(
                $this->generateBrokerExceptionMessage("Server Error"),
                $exception->getCode(),
                $exception
            ),
        };
    }

    /**
     * @param array<mixed> $options
     */
    protected function logResponse(ResponseInterface $response, string $uri, array $options): void
    {
        if (is_null($this->logger)) {
            return;
        }

        $responseCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();
        $requestOptions = json_encode($options);

        $logMessage = "Request URI: {$uri} Request Options: {$requestOptions} Response code: {$responseCode} Response body: {$responseBody}";

        if ($responseCode >= 400) {
            $this->logger->error($logMessage, $options);
        } else {
            $this->logger->debug($logMessage, $options);
        }
        $response->getBody()->rewind();
    }

    /**
     * @return string
     */
    private function generateBrokerExceptionMessage(string $message): string
    {
        return static::BROKER_EXCEPTION_PREFIX . " {$message}";
    }
}
