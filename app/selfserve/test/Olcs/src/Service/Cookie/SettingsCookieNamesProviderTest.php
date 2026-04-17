<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\SettingsCookieNamesProvider;
use Laminas\Http\Header\Cookie;

class SettingsCookieNamesProviderTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $cookie = m::mock(Cookie::class);

        $sut = new SettingsCookieNamesProvider();

        $expected = [
            [
                'name' => 'langPref',
                'domain' => null
            ],
            [
                'name' => 'cookie_seen',
                'domain' => null
            ]
        ];

        $this->assertEquals(
            $expected,
            $sut->getNames($cookie)
        );
    }
}
