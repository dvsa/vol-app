<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\CookieExpiryGenerator;
use Olcs\Service\Cookie\Preferences;
use Olcs\Service\Cookie\SetCookieFactory;
use Olcs\Service\Cookie\PreferencesSetCookieGenerator;
use Laminas\Http\Header\SetCookie;

class PreferencesSetCookieGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $setCookie = m::mock(SetCookie::class);

        $preferencesArray = [
            'analytics' => true,
            'settings' => false,
        ];

        $jsonEncodedPreferences = '{"analytics":true,"settings":false}';

        $cookieExpiry = 1234567;

        $preferences = m::mock(Preferences::class);
        $preferences->shouldReceive('asArray')
            ->withNoArgs()
            ->once()
            ->andReturn($preferencesArray);

        $setCookieFactory = m::mock(SetCookieFactory::class);
        $setCookieFactory->shouldReceive('create')
            ->with(Preferences::COOKIE_NAME, $jsonEncodedPreferences, $cookieExpiry, PreferencesSetCookieGenerator::COOKIE_PATH)
            ->once()
            ->andReturn($setCookie);

        $cookieExpiryGenerator = m::mock(CookieExpiryGenerator::class);
        $cookieExpiryGenerator->shouldReceive('generate')
            ->with('+1 year')
            ->once()
            ->andReturn($cookieExpiry);

        $sut = new PreferencesSetCookieGenerator($setCookieFactory, $cookieExpiryGenerator);

        $this->assertSame(
            $setCookie,
            $sut->generate($preferences)
        );
    }
}
