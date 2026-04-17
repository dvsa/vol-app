<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\CurrentPreferencesProvider;
use Olcs\Service\Cookie\CookieReader;
use Olcs\Service\Cookie\CookieState;
use Olcs\Service\Cookie\Preferences;
use Olcs\Service\Cookie\PreferencesFactory;
use Laminas\Http\Header\Cookie;

class CurrentPreferencesProviderTest extends MockeryTestCase
{
    private $cookieReader;

    private $cookieState;

    private $preferences;

    private $preferencesFactory;

    private $sut;

    public function setUp(): void
    {
        $this->cookieReader = m::mock(CookieReader::class);

        $this->cookieState = m::mock(CookieState::class);

        $this->preferences = m::mock(Preferences::class);

        $this->preferencesFactory = m::mock(PreferencesFactory::class);

        $this->sut = new CurrentPreferencesProvider($this->cookieReader, $this->preferencesFactory);
    }

    public function testReturnStoredPreferencesWhenCookieExists(): void
    {
        $cookie = m::mock(Cookie::class);

        $this->cookieState->shouldReceive('isValid')
            ->withNoArgs()
            ->andReturnTrue();
        $this->cookieState->shouldReceive('getPreferences')
            ->withNoArgs()
            ->andReturn($this->preferences);

        $this->cookieReader->shouldReceive('getState')
            ->with($cookie)
            ->once()
            ->andReturn($this->cookieState);

        $this->assertSame(
            $this->preferences,
            $this->sut->getPreferences($cookie)
        );
    }

    public function testReturnDefaultPreferencesWhenCookieMissingOrInvalid(): void
    {
        $cookie = null;

        $this->cookieState->shouldReceive('isValid')
            ->withNoArgs()
            ->andReturnFalse();

        $this->cookieReader->shouldReceive('getState')
            ->with($cookie)
            ->once()
            ->andReturn($this->cookieState);

        $this->preferencesFactory->shouldReceive('create')
            ->withNoArgs()
            ->once()
            ->andReturn($this->preferences);

        $this->assertSame(
            $this->preferences,
            $this->sut->getPreferences($cookie)
        );
    }
}
