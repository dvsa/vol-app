<?php

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\AcceptAllSetCookieGenerator;
use Olcs\Service\Cookie\Preferences;
use Olcs\Service\Cookie\PreferencesFactory;
use Olcs\Service\Cookie\SetCookieGenerator;
use Zend\Http\Header\SetCookie;

class AcceptAllSetCookieGeneratorTest extends MockeryTestCase
{
    public function testGenerate()
    {
        $setCookie = m::mock(SetCookie::class);

        $preferences = m::mock(Preferences::class);

        $setCookieGenerator = m::mock(SetCookieGenerator::class);
        $setCookieGenerator->shouldReceive('generate')
            ->with($preferences)
            ->once()
            ->andReturn($setCookie);

        $preferencesFactory = m::mock(PreferencesFactory::class);
        $preferencesFactory->shouldReceive('create')
            ->withNoArgs()
            ->once()
            ->andReturn($preferences);

        $sut = new AcceptAllSetCookieGenerator($setCookieGenerator, $preferencesFactory);

        $this->assertSame(
            $setCookie,
            $sut->generate()
        );
    }
}
