<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\CookieState;
use Olcs\Service\Cookie\Preferences;
use RuntimeException;

class CookieStateTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValid')]
    public function testIsValid(bool $isValid): void
    {
        $sut = new CookieState($isValid, m::mock(Preferences::class));

        $this->assertEquals(
            $isValid,
            $sut->isValid()
        );
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{true}, list{false}}
     */
    public static function dpIsValid(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetPreferencesWhenIsValid(): void
    {
        $preferences = m::mock(Preferences::class);

        $sut = new CookieState(true, $preferences);

        $this->assertSame(
            $preferences,
            $sut->getPreferences()
        );
    }

    public function testGetPreferencesWhenIsNotValid(): void
    {
        $this->expectException(RuntimeException::class);

        $sut = new CookieState(false);

        $sut->getPreferences();
    }

    public function testIsActiveWhenIsValid(): void
    {
        $preferences = m::mock(Preferences::class);
        $preferences->shouldReceive('isActive')
            ->with(Preferences::KEY_ANALYTICS)
            ->once()
            ->andReturn(true);
        $preferences->shouldReceive('isActive')
            ->with(Preferences::KEY_SETTINGS)
            ->once()
            ->andReturn(false);

        $sut = new CookieState(true, $preferences);

        $this->assertTrue($sut->isActive(Preferences::KEY_ANALYTICS));
        $this->assertFalse($sut->isActive(Preferences::KEY_SETTINGS));
    }

    public function testIsActiveWhenIsNotValid(): void
    {
        $sut = new CookieState(false);

        $this->assertEquals(
            Preferences::DEFAULT_PREFERENCE_VALUE,
            $sut->isActive(Preferences::KEY_ANALYTICS)
        );

        $this->assertEquals(
            Preferences::DEFAULT_PREFERENCE_VALUE,
            $sut->isActive(Preferences::KEY_SETTINGS)
        );
    }
}
