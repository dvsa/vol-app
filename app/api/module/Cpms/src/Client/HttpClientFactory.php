<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Client;

use Dvsa\Olcs\Cpms\Authenticate\GatewayTokenProviderInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface as Logger;

class HttpClientFactory
{
    public function __construct(
        private readonly ClientOptions $options,
        private readonly Logger $logger,
        private readonly ?GatewayTokenProviderInterface $gatewayTokenProvider = null
    ) {
    }

    public function createHttpClient(): HttpClient
    {
        $clientConfig = [
            'timeout' => $this->options->getTimeout()
        ];

        if ($this->options->getProxy() !== null) {
            $clientConfig['proxy'] = $this->options->getProxy();
        }

        $guzzleHttpClient = new Client($clientConfig);

        return new HttpClient($guzzleHttpClient, $this->options, $this->logger, $this->gatewayTokenProvider);
    }
}
