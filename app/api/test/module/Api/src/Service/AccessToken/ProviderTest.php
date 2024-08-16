<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\AccessToken;

use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class ProviderTest extends TestCase
{
    public function testGetToken(): void
    {
        $tokenString = 'token';
        $token = m::mock(AccessTokenInterface::class);
        $token->expects('getToken')->withNoArgs()->andReturn($tokenString);

        $sut = new Provider($token);
        $this->assertEquals($tokenString, $sut->getToken());
    }
}
