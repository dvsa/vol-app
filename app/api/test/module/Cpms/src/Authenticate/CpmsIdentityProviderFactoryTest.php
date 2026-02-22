<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Authenticate;

use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProvider;
use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProviderFactory;
use PHPUnit\Framework\TestCase;

class CpmsIdentityProviderFactoryTest extends TestCase
{
    public function testCreateCpmsIdentityProvider(): void
    {
        $clientId = 'a-client-id';
        $clientSecret = 'a-client-secret';
        $userId = '123';

        $sut = new CpmsIdentityProviderFactory($clientId, $clientSecret, $userId);
        $cpmsIdentityProvider = $sut->createCpmsIdentityProvider();

        $this->assertInstanceOf(CpmsIdentityProvider::class, $cpmsIdentityProvider);
        $this->assertEquals('123', $cpmsIdentityProvider->getUserId());
        $this->assertEquals('a-client-id', $cpmsIdentityProvider->getClientId());
        $this->assertEquals('a-client-secret', $cpmsIdentityProvider->getClientSecret());
    }
}
