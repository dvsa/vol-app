<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Cpms;

use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use Dvsa\Olcs\Api\Service\Cpms\GatewayTokenProviderAdapter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

final class GatewayTokenProviderAdapterTest extends TestCase
{
    public function testGetTokenDelegatesToProvider(): void
    {
        $provider = m::mock(Provider::class);
        $provider->expects('getToken')->withNoArgs()->andReturn('an-entra-jwt');

        $sut = new GatewayTokenProviderAdapter($provider);

        $this->assertSame('an-entra-jwt', $sut->getToken());
    }
}
