<?php

namespace OlcsTest\Service\Qa;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\Preferences;
use RuntimeException;

class PreferencesTest extends MockeryTestCase
{
    public function testReturnDefaultPreferencesOnEmptyInput()
    {
        $sut = new Preferences([]);

        $expected = [
            Preferences::KEY_ANALYTICS => true,
            Preferences::KEY_SETTINGS => true
        ];

        $this->assertEquals(
            $expected,
            $sut->asArray()
        );
    }

    /**
     * @dataProvider dpExceptionOnInvalidOrMissingKey
     */
    public function testExceptionOnInvalidOrMissingKey($exceptionMessage, $preferencesArray)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Preferences($preferencesArray);
    }

    public function dpExceptionOnInvalidOrMissingKey()
    {
        return [
            [
                'Preference analytics is not present',
                [
                    Preferences::KEY_SETTINGS => true,
                    'key87' => 'foo'
                ]
            ],
            [
                'Preference settings is not present',
                [
                    Preferences::KEY_ANALYTICS => true,
                    'key99' => 'bar'
                ]
            ],
            [
                'Preference analytics is non-bool value',
                [
                    Preferences::KEY_ANALYTICS => 'tree',
                    Preferences::KEY_SETTINGS => true,
                    'key87' => 'foo'
                ]
            ],
            [
                'Preference settings is non-bool value',
                [
                    Preferences::KEY_ANALYTICS => true,
                    Preferences::KEY_SETTINGS => 'cat',
                    'key99' => 'bar'
                ]
            ],
        ];
    }

    /**
     * @dataProvider dpValidInput
     */
    public function testValidInput($preferencesArray)
    {
        $sut = new Preferences($preferencesArray);

        $this->assertEquals(
            $preferencesArray,
            $sut->asArray()
        );
    }

    public function dpValidInput()
    {
        return [
            [
                [
                    Preferences::KEY_ANALYTICS => true,
                    Preferences::KEY_SETTINGS => true,
                ]
            ],
            [
                [
                    Preferences::KEY_ANALYTICS => true,
                    Preferences::KEY_SETTINGS => false,
                ]
            ],
            [
                [
                    Preferences::KEY_ANALYTICS => false,
                    Preferences::KEY_SETTINGS => true,
                ]
            ],
            [
                [
                    Preferences::KEY_ANALYTICS => false,
                    Preferences::KEY_SETTINGS => false,
                ]
            ],
        ];
    }

    public function testIsActive()
    {
        $sut = new Preferences(
            [
                Preferences::KEY_ANALYTICS => true,
                Preferences::KEY_SETTINGS => false,
            ]
        );

        $this->assertTrue($sut->isActive(Preferences::KEY_ANALYTICS));
        $this->assertFalse($sut->isActive(Preferences::KEY_SETTINGS));
    }
}
