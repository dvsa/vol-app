<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\CookieNamesProviderInterface;
use Olcs\Service\Cookie\DeleteCookieNamesProvider;
use Olcs\Service\Cookie\Preferences;
use Laminas\Http\Header\Cookie;

class DeleteCookieNamesProviderTest extends MockeryTestCase
{
    public function testGetNames(): void
    {
        $cookie = m::mock(Cookie::class);

        $preferences = m::mock(Preferences::class);
        $preferences->shouldReceive('isActive')
            ->with('settings')
            ->andReturn(false);
        $preferences->shouldReceive('isActive')
            ->with('analytics')
            ->andReturn(true);
        $preferences->shouldReceive('isActive')
            ->with('other')
            ->andReturn(false);

        $settingsCookieNames = [
            [
                'name' => 'settings_cookie_name_1_name',
                'domain' => 'settings_cookie_name_1_domain'
            ],
            [
                'name' => 'settings_cookie_2_name',
                'domain' => 'settings_cookie_2_domain'
            ],
        ];

        $settingsCookieNamesProvider = m::mock(CookieNamesProviderInterface::class);
        $settingsCookieNamesProvider->shouldReceive('getNames')
            ->with($cookie)
            ->andReturn($settingsCookieNames);

        $analyticsCookieNamesProvider = m::mock(CookieNamesProviderInterface::class);

        $otherCookieNames = [
            [
                'name' => 'other_cookie_name_1_name',
                'domain' => 'other_cookie_name_1_domain'
            ],
            [
                'name' => 'other_cookie_2_name',
                'domain' => 'other_cookie_2_domain'
            ],
        ];

        $otherCookieNamesProvider = m::mock(CookieNamesProviderInterface::class);
        $otherCookieNamesProvider->shouldReceive('getNames')
            ->with($cookie)
            ->andReturn($otherCookieNames);

        $expectedCookieNames = [
            [
                'name' => 'settings_cookie_name_1_name',
                'domain' => 'settings_cookie_name_1_domain'
            ],
            [
                'name' => 'settings_cookie_2_name',
                'domain' => 'settings_cookie_2_domain'
            ],
            [
                'name' => 'other_cookie_name_1_name',
                'domain' => 'other_cookie_name_1_domain'
            ],
            [
                'name' => 'other_cookie_2_name',
                'domain' => 'other_cookie_2_domain'
            ],
        ];

        $sut = new DeleteCookieNamesProvider();
        $sut->registerCookieNamesProvider('settings', $settingsCookieNamesProvider);
        $sut->registerCookieNamesProvider('analytics', $analyticsCookieNamesProvider);
        $sut->registerCookieNamesProvider('other', $otherCookieNamesProvider);

        $this->assertEquals(
            $expectedCookieNames,
            $sut->getNames($preferences, $cookie)
        );
    }
}
