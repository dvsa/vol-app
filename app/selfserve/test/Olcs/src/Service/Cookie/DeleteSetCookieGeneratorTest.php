<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\CookieExpiryGenerator;
use Olcs\Service\Cookie\SetCookieFactory;
use Olcs\Service\Cookie\DeleteSetCookieGenerator;
use Laminas\Http\Header\SetCookie;

class DeleteSetCookieGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $setCookie = m::mock(SetCookie::class);
        $dataName = 'cookieName';
        $dataDomain = 'cookieDomain';

        $data = [
            'name' => $dataName,
            'domain' => $dataDomain,
        ];

        $cookieExpiry = 7654321;

        $setCookieFactory = m::mock(SetCookieFactory::class);
        $setCookieFactory->shouldReceive('create')
            ->with($dataName, '', $cookieExpiry, DeleteSetCookieGenerator::COOKIE_PATH, $dataDomain)
            ->once()
            ->andReturn($setCookie);

        $cookieExpiryGenerator = m::mock(CookieExpiryGenerator::class);
        $cookieExpiryGenerator->shouldReceive('generate')
            ->with('-1 year')
            ->once()
            ->andReturn($cookieExpiry);

        $sut = new DeleteSetCookieGenerator($setCookieFactory, $cookieExpiryGenerator);

        $this->assertSame(
            $setCookie,
            $sut->generate($data)
        );
    }
}
