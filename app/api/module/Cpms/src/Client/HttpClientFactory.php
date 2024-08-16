<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Client;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface as Logger;

class HttpClientFactory
{
    public function __construct(private readonly ClientOptions $options, private readonly Logger $logger)
    {
    }

    public function createHttpClient(): HttpClient
    {
        $clientConfig = [
            'timeout' => $this->options->getTimeout()
        ];

        $guzzleHttpClient = new Client($clientConfig);

        return new HttpClient($guzzleHttpClient, $this->options, $this->logger);
    }
}
