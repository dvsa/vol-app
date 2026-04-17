<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\BannerVisibilityProvider;
use Olcs\Service\Cookie\CookieReader;
use Olcs\Service\Cookie\CookieState;
use Laminas\Http\Header\Cookie;
use Laminas\Mvc\MvcEvent;

class BannerVisibilityProviderTest extends MockeryTestCase
{
    private $cookieReader;

    private $mvcEvent;

    private $sut;

    public function setUp(): void
    {
        $this->cookieReader = m::mock(CookieReader::class);

        $this->mvcEvent = m::mock(MvcEvent::class);

        $this->sut = new BannerVisibilityProvider($this->cookieReader);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpFalseOnExemptRoute')]
    public function testFalseOnExemptRoute(string $routeName): void
    {
        $this->mvcEvent->shouldReceive('getRouteMatch->getMatchedRouteName')
            ->withNoArgs()
            ->andReturn($routeName);

        $this->assertFalse(
            $this->sut->shouldDisplay($this->mvcEvent)
        );
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'cookies/settings'}}
     */
    public static function dpFalseOnExemptRoute(): array
    {
        return [
            ['cookies/settings'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpNonExemptRoute')]
    public function testNonExemptRoute(bool $cookieStateIsValid, bool $expected): void
    {
        $cookie = m::mock(Cookie::class);

        $this->mvcEvent->shouldReceive('getRouteMatch->getMatchedRouteName')
            ->withNoArgs()
            ->andReturn('route66');

        $this->mvcEvent->shouldReceive('getRequest->getCookie')
            ->withNoArgs()
            ->andReturn($cookie);

        $cookieState = m::mock(CookieState::class);
        $cookieState->shouldReceive('isValid')
            ->withNoArgs()
            ->andReturn($cookieStateIsValid);

        $this->cookieReader->shouldReceive('getState')
            ->with($cookie)
            ->andReturn($cookieState);

        $this->assertEquals(
            $expected,
            $this->sut->shouldDisplay($this->mvcEvent)
        );
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{true, false}, list{false, true}}
     */
    public static function dpNonExemptRoute(): array
    {
        return [
            [true, false],
            [false, true],
        ];
    }
}
