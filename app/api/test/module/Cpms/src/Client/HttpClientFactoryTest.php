<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Client;

use Dvsa\Olcs\Cpms\Client\HttpClient;
use Dvsa\Olcs\Cpms\Client\HttpClientFactory;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class HttpClientFactoryTest extends TestCase
{
    use ClientOptionsTestTrait;

    public function testCreateHttpClient(): void
    {
        $sut = new HttpClientFactory(
            $this->getClientOptions(),
            new Logger('cpms_client_test_logger')
        );
        $client = $sut->createHttpClient();
        $this->assertInstanceOf(HttpClient::class, $client);
    }
}
