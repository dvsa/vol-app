<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Client;

use Dvsa\Olcs\Cpms\Client\HttpClient;
use Dvsa\Olcs\Cpms\Client\HttpClientFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class HttpClientFactoryTest extends TestCase
{
    use ClientOptionsTestTrait;

    public function testCreateHttpClient(): void
    {
        $sut = new HttpClientFactory(
            $this->getClientOptions(),
            new NullLogger()
        );
        $client = $sut->createHttpClient();
        $this->assertInstanceOf(HttpClient::class, $client);
    }
}
