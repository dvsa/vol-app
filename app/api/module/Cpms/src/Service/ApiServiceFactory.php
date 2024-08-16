<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Service;

use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProvider;
use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProviderFactory;
use Dvsa\Olcs\Cpms\Client\ClientOptions;
use Dvsa\Olcs\Cpms\Client\HttpClient;
use Dvsa\Olcs\Cpms\Client\HttpClientFactory;
use Dvsa\Olcs\Cpms\Logger\LoggerFactory;
use Psr\Log\LoggerInterface as Logger;
use RuntimeException;

class ApiServiceFactory
{
    public function __construct(private array $config, private readonly string $userId)
    {
    }

    public function createApiService(): ApiService
    {
        /** @var ApiService $service */
        $service = new ApiService(
            $this->returnHttpClient(),
            $this->returnIdentity(),
            $this->returnLogger()
        );

        return $service;
    }

    private function returnLogger(): Logger
    {
        $logPath = $this->config['log']['Logger']['writers']['full']['options']['stream'];
        $zendLogLevel = $this->config['log']['Logger']['writers']['full']['options']['filters']['priority']['options']['priority'];
        $loggerFactory = new LoggerFactory($logPath, $zendLogLevel);
        return $loggerFactory->createLogger();
    }

    private function returnIdentity(): CpmsIdentityProvider
    {
        if (
            empty($this->config['cpms_credentials']) ||
            empty($this->config['cpms_credentials']['client_id']) ||
            empty($this->config['cpms_credentials']['client_secret'])
        ) {
            throw new RuntimeException('Missing required CPMS credentials');
        }

        /** @var CpmsIdentityProvider $identity */
        $identityFactory = new CpmsIdentityProviderFactory(
            $this->config['cpms_credentials']['client_id'],
            $this->config['cpms_credentials']['client_secret'],
            $this->userId
        );

        return $identityFactory->createCpmsIdentityProvider();
    }

    public function returnHttpClient(): HttpClient
    {
        $options = $this->config['cpms_api']['rest_client']['options'];

        if (empty($options)) {
            throw new RuntimeException('Missing required CPMS client options');
        }

        $clientOptions = new ClientOptions(
            $options['version'],
            $options['grant_type'],
            $options['timeout'],
            $options['domain'],
            $options['headers']
        );

        /** @var HttpClient $httpClient */
        $httpClientFactory = new HttpClientFactory($clientOptions, $this->returnLogger());
        return $httpClientFactory->createHttpClient();
    }
}
