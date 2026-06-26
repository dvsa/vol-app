<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\PasswordService;
use Dvsa\Olcs\Auth\Service\Auth\PasswordServiceFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see PasswordServiceFactory
 */
class PasswordServiceFactoryTest extends MockeryTestCase
{
    public function testMissingRealmConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(PasswordServiceFactory::MSG_MISSING_REALM);

        $config = [
            'auth' => [],
        ];

        $mockContainer = m::mock(ContainerInterface::class);
        $mockContainer->expects('get')->with('Config')->andReturn($config);

        $sut = new PasswordServiceFactory();
        $sut($mockContainer, PasswordService::class);
    }
}
