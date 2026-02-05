<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\Preferences;
use RuntimeException;

class PreferencesTest extends MockeryTestCase
{
    public function testReturnDefaultPreferencesOnEmptyInput(): void
    {
        $sut = new Preferences([]);

        $expected = [
            Preferences::KEY_ANALYTICS => false,
            Preferences::KEY_SETTINGS => false
        ];

        $this->assertEquals(
            $expected,
            $sut->asArray()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpExceptionOnInvalidOrMissingKey')]
    public function testExceptionOnInvalidOrMissingKey(string $exceptionMessage, array $preferencesArray): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Preferences($preferencesArray);
    }

    /**
     * @return ((string|true)[]|string)[][]
     *
     * @psalm-return list{list{'Preference analytics is not present', array{settings: true, key87: 'foo'}}, list{'Preference settings is not present', array{analytics: true, key99: 'bar'}}, list{'Preference analytics is non-bool value', array{analytics: 'tree', settings: true, key87: 'foo'}}, list{'Preference settings is non-bool value', array{analytics: true, settings: 'cat', key99: 'bar'}}}
     */
    public static function dpExceptionOnInvalidOrMissingKey(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpValidInput')]
    public function testValidInput(array $preferencesArray): void
    {
        $sut = new Preferences($preferencesArray);

        $this->assertEquals(
            $preferencesArray,
            $sut->asArray()
        );
    }

    /**
     * @return bool[][][]
     *
     * @psalm-return list{list{array{analytics: true, settings: true}}, list{array{analytics: true, settings: false}}, list{array{analytics: false, settings: true}}, list{array{analytics: false, settings: false}}}
     */
    public static function dpValidInput(): array
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

    public function testIsActive(): void
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
