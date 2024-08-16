<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Client;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class HttpClient
 */
class HttpClient
{
    public const CONTENT_TYPE_FORMAT = 'application/vnd.dvsa-gov-uk.v%d%s; charset=UTF-8';

    public const METHOD_GET = 'get';
    public const METHOD_PUT = 'put';
    public const METHOD_POST = 'post';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ClientOptions
     */
    protected $clientOptions;

    protected $logger;

    public function __construct(Client $client, ClientOptions $clientOptions, Logger $logger)
    {
        $this->client = $client;
        $this->clientOptions = $clientOptions;
        $this->logger = $logger;
    }

    public function get(string $endpoint, array $data)
    {
        $uri = $this->buildURI($endpoint);
        $response = $this->client->get($uri, $this->buildOptions(self::METHOD_GET, $data));

        $this->logResponse($response, $uri);

        return $this->decodeResponse($response);
    }

    public function post(string $endpoint, array $data)
    {
        $uri = $this->buildURI($endpoint);
        $response = $this->client->post($uri, $this->buildOptions(self::METHOD_POST, $data));

        $this->logResponse($response, $uri);

        return $this->decodeResponse($response);
    }

    public function put(string $endpoint, array $data)
    {
        $uri = $this->buildURI($endpoint);
        $response = $this->client->put($uri, $this->buildOptions(self::METHOD_PUT, $data));

        $this->logResponse($response, $uri);

        return $this->decodeResponse($response);
    }

    public function resetHeaders(): void
    {
        $headers = $this->clientOptions->getHeaders();

        if (isset($headers['Authorization'])) {
            unset($headers['Authorization']);
        }

        $this->clientOptions->setHeaders($headers);
    }

    public function getClientOptions(): ClientOptions
    {
        return $this->clientOptions;
    }


    /**
     * @return mixed
     */
    protected function decodeResponse(ResponseInterface $response)
    {
        $response->getBody()->rewind();
        $responseBody = $response->getBody()->getContents();

        $decoded = json_decode($responseBody, true);

        if (empty($decoded) && json_last_error() !== JSON_ERROR_NONE) {
            return $responseBody;
        }

        return $decoded;
    }

    protected function buildOptions(string $method, array $data): array
    {
        $options = match ($method) {
            self::METHOD_PUT, self::METHOD_POST => $this->buildPostOrPutQuery($this->sanitiseDataCharset($data), $method),
            default => $this->buildGetQuery($data),
        };

        $options['headers'] = array_merge($options['headers'], $this->buildHeaders());
        return $options;
    }

    protected function buildGetQuery(array $data): array
    {
        return [
            'headers' => [
                'Content-Type' => $this->getContentType(self::METHOD_GET)
            ],
            'query' => $data
        ];
    }

    protected function buildPostOrPutQuery(array $data, string $method): array
    {
        return [
            'headers' => [
                'Content-Type' => $this->getContentType($method)
            ],
            'body' => json_encode($data)
        ];
    }

    /**
     * Sanitizes strings submitted in JSON payload to safe charset for SAP export
     *
     * @return array
     */
    protected function sanitiseDataCharset(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                ini_set('mbstring.substitute_character', "none");
                $value = mb_convert_encoding($value, 'ASCII', mb_detect_encoding($value));
            }
        });
        return $data;
    }

    protected function getContentType(string $method): string
    {
        $httpMethods = [
            self::METHOD_POST,
            self::METHOD_PUT
        ];
        $version = $this->clientOptions->getVersion();
        $format = (in_array($method, $httpMethods)) ? "+json" : "";
        return sprintf(static::CONTENT_TYPE_FORMAT, $version, $format);
    }

    protected function buildHeaders(): array
    {
        return $this->clientOptions->getHeaders();
    }

    protected function buildURI(string $endpoint): string
    {
        $uri = rtrim($this->clientOptions->getDomain(), '/') . '/' . ltrim($endpoint, '/');

        return $uri;
    }

    protected function logResponse(ResponseInterface $response, string $uri): void
    {
        $response->getBody()->rewind();
        $responseCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();
        $filteredResponseBody = $this->filterResponseBody($responseBody);

        $detailedLogMessage = "Request URI: $uri" . "\n Response code: " . $responseCode . "\n Response body: " . $filteredResponseBody;
        $shortLogMessage = "Request URI: $uri" . "\n Response code: " . $responseCode;

        if ($responseCode >= 400) {
            $this->logger->error($shortLogMessage);
        } else {
            $this->logger->info($shortLogMessage);
            $this->logger->debug($detailedLogMessage);
        }
    }

    protected function filterResponseBody(string $responseBody): ?string
    {
        return preg_replace('/(access_token":")([\d|\w]*)/', '$1****', $responseBody);
    }
}
