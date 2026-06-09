<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Service;

use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProvider;
use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProviderFactory;
use Dvsa\Olcs\Cpms\Client\ClientOptions;
use Dvsa\Olcs\Cpms\Client\HttpClient;
use Dvsa\Olcs\Cpms\Client\HttpClientFactory;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ApiServiceFactory
{
    public function __construct(
        private array $config,
        private readonly string $userId,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function createApiService(): ApiService
    {
        return new ApiService(
            $this->returnHttpClient(),
            $this->returnIdentity(),
            $this->logger,
        );
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

        $httpClientFactory = new HttpClientFactory($clientOptions, $this->logger);
        return $httpClientFactory->createHttpClient();
    }
}
