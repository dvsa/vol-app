<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\DeleteCookieNamesProvider;
use Olcs\Service\Cookie\DeleteSetCookieGenerator;
use Olcs\Service\Cookie\Preferences;
use Olcs\Service\Cookie\PreferencesSetCookieGenerator;
use Olcs\Service\Cookie\SetCookieArrayGenerator;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Header\SetCookie;

class SetCookieArrayGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $deleteSetCookie1 = m::mock(SetCookie::class);
        $deleteSetCookie2 = m::mock(SetCookie::class);
        $preferencesSetCookie = m::mock(SetCookie::class);

        $cookieName1 = [
            'name' => 'cookieName1',
            'domain' => 'cookieDomain1'
        ];

        $cookieName2 = [
            'name' => 'cookieName2',
            'domain' => 'cookieDomain2'
        ];

        $cookieNames = [
            $cookieName1,
            $cookieName2
        ];

        $preferences = m::mock(Preferences::class);

        $cookie = m::mock(Cookie::class);

        $deleteCookieNamesProvider = m::mock(DeleteCookieNamesProvider::class);
        $deleteCookieNamesProvider->shouldReceive('getNames')
            ->with($preferences, $cookie)
            ->andReturn($cookieNames);

        $preferencesSetCookieGenerator = m::mock(PreferencesSetCookieGenerator::class);
        $preferencesSetCookieGenerator->shouldReceive('generate')
            ->with($preferences)
            ->andReturn($preferencesSetCookie);

        $deleteSetCookieGenerator = m::mock(DeleteSetCookieGenerator::class);
        $deleteSetCookieGenerator->shouldReceive('generate')
            ->with($cookieName1)
            ->andReturn($deleteSetCookie1);
        $deleteSetCookieGenerator->shouldReceive('generate')
            ->with($cookieName2)
            ->andReturn($deleteSetCookie2);

        $sut = new SetCookieArrayGenerator(
            $deleteCookieNamesProvider,
            $preferencesSetCookieGenerator,
            $deleteSetCookieGenerator
        );

        $setCookies = $sut->generate($preferences, $cookie);

        $this->assertCount(3, $setCookies);
        $this->assertContains($deleteSetCookie1, $setCookies);
        $this->assertContains($deleteSetCookie2, $setCookies);
        $this->assertContains($preferencesSetCookie, $setCookies);
    }
}
