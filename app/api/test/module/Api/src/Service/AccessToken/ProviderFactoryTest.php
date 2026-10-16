<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\AccessToken;

use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use Dvsa\Olcs\Api\Service\AccessToken\ProviderFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

final class ProviderFactoryTest extends TestCase
{
    public function testInvokeReturnsProviderWithoutFetchingToken(): void
    {
        // the pre-refactor factory fetched eagerly and would fail on this unreachable token_url
        $options = [
            'client_id' => 'an-id',
            'client_secret' => 'a-secret',
            'token_url' => 'https://token.invalid/token',
            'scope' => 'api://an-app/.default',
            'proxy' => 'http://proxy.local:3128',
            'service_name' => 'a-service',
        ];

        $sut = new ProviderFactory();
        $provider = $sut->__invoke(m::mock(ContainerInterface::class), Provider::class, $options);

        $this->assertInstanceOf(Provider::class, $provider);
    }

    public function testInvokeToleratesMissingOptionalKeys(): void
    {
        $options = [
            'client_id' => 'an-id',
            'client_secret' => 'a-secret',
            'token_url' => 'https://token.invalid/token',
            'scope' => 'api://an-app/.default',
        ];

        $sut = new ProviderFactory();
        $this->assertInstanceOf(Provider::class, $sut->__invoke(m::mock(ContainerInterface::class), Provider::class, $options));
    }
}
