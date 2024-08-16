<?php

namespace Dvsa\OlcsTest\Cpms\Client;

use Dvsa\Olcs\Cpms\Client\HttpClient;
use Dvsa\Olcs\Cpms\Client\HttpClientFactory;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class HttpClientFactoryTest extends TestCase
{
    use ClientOptionsTestTrait;

    public function testCreateHttpClient()
    {
        $sut = new HttpClientFactory(
            $this->getClientOptions(),
            new Logger('cpms_client_test_logger')
        );
        $client = $sut->createHttpClient();
        $this->assertInstanceOf(HttpClient::class, $client);
    }
}
