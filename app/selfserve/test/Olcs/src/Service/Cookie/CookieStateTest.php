<?php

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\CookieState;
use Olcs\Service\Cookie\Preferences;
use RuntimeException;

class CookieStateTest extends MockeryTestCase
{
    /**
     * @dataProvider dpIsValid
     */
    public function testIsValid($isValid)
    {
        $sut = new CookieState($isValid, m::mock(Preferences::class));

        $this->assertEquals(
            $isValid,
            $sut->isValid()
        );
    }

    public function dpIsValid()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetPreferencesWhenIsValid()
    {
        $preferences = m::mock(Preferences::class);

        $sut = new CookieState(true, $preferences);

        $this->assertSame(
            $preferences,
            $sut->getPreferences()
        );
    }

    public function testGetPreferencesWhenIsNotValid()
    {
        $this->expectException(RuntimeException::class);

        $sut = new CookieState(false);

        $sut->getPreferences();
    }
}
