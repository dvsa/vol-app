<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\AcceptAllSetCookieGenerator;
use Olcs\Service\Cookie\Preferences;
use Olcs\Service\Cookie\PreferencesFactory;
use Olcs\Service\Cookie\PreferencesSetCookieGenerator;
use Laminas\Http\Header\SetCookie;

class AcceptAllSetCookieGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $setCookie = m::mock(SetCookie::class);

        $preferences = m::mock(Preferences::class);

        $preferencesSetCookieGenerator = m::mock(PreferencesSetCookieGenerator::class);
        $preferencesSetCookieGenerator->shouldReceive('generate')
            ->with($preferences)
            ->once()
            ->andReturn($setCookie);

        $expectedPreferencesArray = [
            Preferences::KEY_ANALYTICS => true,
            Preferences::KEY_SETTINGS => true,
        ];

        $preferencesFactory = m::mock(PreferencesFactory::class);
        $preferencesFactory->shouldReceive('create')
            ->with($expectedPreferencesArray)
            ->once()
            ->andReturn($preferences);

        $sut = new AcceptAllSetCookieGenerator($preferencesSetCookieGenerator, $preferencesFactory);

        $this->assertSame(
            $setCookie,
            $sut->generate()
        );
    }
}
