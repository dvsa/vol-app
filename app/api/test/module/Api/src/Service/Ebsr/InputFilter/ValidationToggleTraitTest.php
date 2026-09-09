<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\InputFilter;

use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ValidationToggleTrait;
use PHPUnit\Framework\TestCase;

final class ValidationToggleTraitTest extends TestCase
{
    private object $sut;

    public function setUp(): void
    {
        $this->sut = new class {
            use ValidationToggleTrait;

            public function isEnabled(array $config, string $inputName): bool
            {
                return $this->isValidationEnabled($config, $inputName);
            }
        };
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('settingProvider')]
    public function testIsValidationEnabled(mixed $setting, bool $expected): void
    {
        $config = ['ebsr' => ['validate' => ['bus_registration' => $setting]]];

        $this->assertSame($expected, $this->sut->isEnabled($config, 'bus_registration'));
    }

    /**
     * @return \Iterator<string, array{mixed, bool}>
     */
    public static function settingProvider(): \Iterator
    {
        //the only values that switch validation off are the ones that unambiguously read as false
        yield 'boolean false' => [false, false];
        yield 'string false' => ['false', false];
        yield 'string zero' => ['0', false];
        yield 'integer zero' => [0, false];
        yield 'string off' => ['off', false];
        yield 'string no' => ['no', false];

        //everything else leaves validation on, so a mistyped setting cannot let packs through unchecked
        yield 'boolean true' => [true, true];
        yield 'string true' => ['true', true];
        yield 'string one' => ['1', true];
        yield 'integer one' => [1, true];
        yield 'unrecognised string' => ['disabled', true];
        yield 'empty string' => ['', true];
        yield 'null' => [null, true];
        yield 'array' => [[], true];
    }

    public function testIsValidationEnabledWhenNotConfigured(): void
    {
        $this->assertTrue($this->sut->isEnabled([], 'bus_registration'));
        $this->assertTrue($this->sut->isEnabled(['ebsr' => ['validate' => []]], 'bus_registration'));
    }
}
