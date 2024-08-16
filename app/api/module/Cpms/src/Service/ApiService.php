<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Service;

use Dvsa\Olcs\Cpms\Authenticate\AccessToken;
use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProvider;
use Dvsa\Olcs\Cpms\Client\ClientOptions;
use Dvsa\Olcs\Cpms\Client\HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface as Logger;

class ApiService
{
    public const SCOPE_CARD = 'CARD';
    public const SCOPE_CNP = 'CNP';
    public const SCOPE_DIRECT_DEBIT = 'DIRECT_DEBIT';
    public const SCOPE_CHEQUE = 'CHEQUE';
    public const SCOPE_REFUND = 'REFUND';
    public const SCOPE_QUERY_TXN = 'QUERY_TXN';
    public const SCOPE_STORED_CARD = 'STORED_CARD';
    public const SCOPE_CHARGE_BACK = 'CHARGE_BACK';
    public const SCOPE_CASH = 'CASH';
    public const SCOPE_POSTAL_ORDER = 'POSTAL_ORDER';
    public const SCOPE_CHIP_PIN = 'CHIP_PIN';
    public const SCOPE_ADJUSTMENT = 'ADJUSTMENT';
    public const SCOPE_REPORT = 'REPORT';
    public const CHEQUE_RD = 'CHEQUE_RD'; // refer to drawer
    public const DIRECT_DEBIT_IC = 'DIRECT_DEBIT_IC'; // indemnity claim
    public const REALLOCATE_PAYMENT = 'REALLOCATE'; // Reallocate payments by switch customer reference

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var ClientOptions
     */
    protected $options;

    /**
     * @var CpmsIdentityProvider
     */
    protected $identity;


    /**
     * @var StreamInterface | string
     */
    private $errorResponseBody;


    public function __construct(
        HttpClient $httpClient,
        CpmsIdentityProvider $identity,
        Logger $logger
    ) {
        $this->httpClient = $httpClient;
        $this->identity = $identity;
        $this->options = $httpClient->getClientOptions();
        $this->logger = $logger;
    }

    /**
     *
     * @return array|mixed
     */
    public function get(string $endPoint, string $scope, array $data = [])
    {
        return $this->getAuthTokenAndProcessRequest($endPoint, $scope, HttpClient::METHOD_GET, $data);
    }


    /**
     *
     * @return array|mixed
     */
    public function post(string $endPoint, string $scope, array $data)
    {
        return $this->getAuthTokenAndProcessRequest($endPoint, $scope, HttpClient::METHOD_POST, $data);
    }

    /**
     *
     * @return array|mixed
     */
    public function put(string $endPoint, string $scope, array $data)
    {
        return $this->getAuthTokenAndProcessRequest($endPoint, $scope, HttpClient::METHOD_PUT, $data);
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * @return ClientOptions
     */
    public function getOptions(): ClientOptions
    {
        return $this->options;
    }

    /**
     * @return CpmsIdentityProvider
     */
    public function getIdentity(): CpmsIdentityProvider
    {
        return $this->identity;
    }

    /**
     * Process API request
     *
     * @param string     $scope  (CARD, DIRECT_DEBIT)
     * @param string     $method HTTP Method (GET, POST, PUT)
     *
     * @return array|mixed
     */
    protected function getAuthTokenAndProcessRequest(
        string $endPoint,
        string $scope,
        string $method,
        ?array $params = null
    ) {
        $salesReference = $this->getSalesReferenceFromParams($params);

        $message = 'CPMS error empty body';
        $messageObj = "{}";

        try {
            //Get access token
            $token = $this->getCpmsAccessToken($scope, $salesReference);

            if ($token instanceof AccessToken) {
                $headers = $this->getOptions()->getHeaders();
                $headers['Authorization'] = $token->getAuthorisationHeader();
                $this->getOptions()->setHeaders($headers);
                $response = $this->getHttpClient()->$method($endPoint, $params);

                if (empty($response)) {
                    throw new \Exception($message);
                }
                return $response;
            } else {
                return $token;
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                if ($response instanceof Response) {
                    $this->errorResponseBody = $response->getBody();
                    $this->errorResponseBody->rewind();
                    $messageObj = json_decode($this->errorResponseBody->getContents());
                }
            }
            $message = $messageObj->message ?? $e->getMessage();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return $this->returnErrorMessage($message);
    }

    /**
     *
     * @return AccessToken|mixed
     */
    protected function getCpmsAccessToken(string $scope, ?string $salesReference = null)
    {
        $payload = [
            'client_id' => $this->getIdentity()->getClientId(),
            'client_secret' => $this->getIdentity()->getClientSecret(),
            'user_id' => $this->getIdentity()->getUserId(),
            'grant_type' => $this->getOptions()->getGrantType(),
            'scope' => $scope,
        ];

        if (!empty($salesReference)) {
            $payload['sales_reference'] = $salesReference;
        }

        $client = $this->getHttpClient();

        $response = $client->post('/api/token', $payload);

        if (isset($response['access_token'])) {
            return new AccessToken(
                $response['access_token'],
                $response['expires_in'],
                time(),
                $response['scope'],
                $response['token_type'],
                $response['sales_reference']
            );
        }

        $this->logger->warning('Unable to get access token with data: ' . print_r($response, true));
        return $response;
    }

    private function returnErrorMessage(string $message): string
    {
        $logMessage = sprintf("An error occurred, ID %s\n%s", $this->getErrorId(), $message);
        $this->logger->error($logMessage);

        return $message;
    }

    /**
     * @return string|null
     */
    private function getSalesReferenceFromParams(?array $params): ?string
    {
        if (!isset($params['payment_data'])) {
            return null;
        }

        $paymentRow = current($params['payment_data']);

        if (is_array($paymentRow) && isset($paymentRow['sales_reference'])) {
            return $paymentRow['sales_reference'];
        }
        return null;
    }

    /**
     * Return a unique identifier for the error message for tracking in the the logs
     */
    private function getErrorId(): string
    {
        return md5(uniqid('API'));
    }
}
